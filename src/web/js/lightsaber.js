/*
 * $Id: rawinflate.js,v 0.2 2009/03/01 18:32:24 dankogai Exp $
 *
 * original:
 * http://www.onicos.com/staff/iz/amuse/javascript/expert/inflate.txt
 */

(function(){

/* Copyright (C) 1999 Masanao Izumo <iz@onicos.co.jp>
 * Version: 1.0.0.1
 * LastModified: Dec 25 1999
 */

/* Interface:
 * data = zip_inflate(src);
 */

/* constant parameters */
var zip_WSIZE = 32768;		// Sliding Window size
var zip_STORED_BLOCK = 0;
var zip_STATIC_TREES = 1;
var zip_DYN_TREES    = 2;

/* for inflate */
var zip_lbits = 9; 		// bits in base literal/length lookup table
var zip_dbits = 6; 		// bits in base distance lookup table
var zip_INBUFSIZ = 32768;	// Input buffer size
var zip_INBUF_EXTRA = 64;	// Extra buffer

/* variables (inflate) */
var zip_slide;
var zip_wp;			// current position in slide
var zip_fixed_tl = null;	// inflate static
var zip_fixed_td;		// inflate static
var zip_fixed_bl, fixed_bd;	// inflate static
var zip_bit_buf;		// bit buffer
var zip_bit_len;		// bits in bit buffer
var zip_method;
var zip_eof;
var zip_copy_leng;
var zip_copy_dist;
var zip_tl, zip_td;	// literal/length and distance decoder tables
var zip_bl, zip_bd;	// number of bits decoded by tl and td

var zip_inflate_data;
var zip_inflate_pos;


/* constant tables (inflate) */
var zip_MASK_BITS = new Array(
    0x0000,
    0x0001, 0x0003, 0x0007, 0x000f, 0x001f, 0x003f, 0x007f, 0x00ff,
    0x01ff, 0x03ff, 0x07ff, 0x0fff, 0x1fff, 0x3fff, 0x7fff, 0xffff);
// Tables for deflate from PKZIP's appnote.txt.
var zip_cplens = new Array( // Copy lengths for literal codes 257..285
    3, 4, 5, 6, 7, 8, 9, 10, 11, 13, 15, 17, 19, 23, 27, 31,
    35, 43, 51, 59, 67, 83, 99, 115, 131, 163, 195, 227, 258, 0, 0);
/* note: see note #13 above about the 258 in this list. */
var zip_cplext = new Array( // Extra bits for literal codes 257..285
    0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 2, 2, 2, 2,
    3, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 0, 99, 99); // 99==invalid
var zip_cpdist = new Array( // Copy offsets for distance codes 0..29
    1, 2, 3, 4, 5, 7, 9, 13, 17, 25, 33, 49, 65, 97, 129, 193,
    257, 385, 513, 769, 1025, 1537, 2049, 3073, 4097, 6145,
    8193, 12289, 16385, 24577);
var zip_cpdext = new Array( // Extra bits for distance codes
    0, 0, 0, 0, 1, 1, 2, 2, 3, 3, 4, 4, 5, 5, 6, 6,
    7, 7, 8, 8, 9, 9, 10, 10, 11, 11,
    12, 12, 13, 13);
var zip_border = new Array(  // Order of the bit length code lengths
    16, 17, 18, 0, 8, 7, 9, 6, 10, 5, 11, 4, 12, 3, 13, 2, 14, 1, 15);
/* objects (inflate) */

var zip_HuftList = function() {
    this.next = null;
    this.list = null;
}

var zip_HuftNode = function() {
    this.e = 0; // number of extra bits or operation
    this.b = 0; // number of bits in this code or subcode

    // union
    this.n = 0; // literal, length base, or distance base
    this.t = null; // (zip_HuftNode) pointer to next level of table
}

var zip_HuftBuild = function(b,	// code lengths in bits (all assumed <= BMAX)
		       n,	// number of codes (assumed <= N_MAX)
		       s,	// number of simple-valued codes (0..s-1)
		       d,	// list of base values for non-simple codes
		       e,	// list of extra bits for non-simple codes
		       mm	// maximum lookup bits
		   ) {
    this.BMAX = 16;   // maximum bit length of any code
    this.N_MAX = 288; // maximum number of codes in any set
    this.status = 0;	// 0: success, 1: incomplete table, 2: bad input
    this.root = null;	// (zip_HuftList) starting table
    this.m = 0;		// maximum lookup bits, returns actual

/* Given a list of code lengths and a maximum table size, make a set of
   tables to decode that set of codes.	Return zero on success, one if
   the given code set is incomplete (the tables are still built in this
   case), two if the input is invalid (all zero length codes or an
   oversubscribed set of lengths), and three if not enough memory.
   The code with value 256 is special, and the tables are constructed
   so that no bits beyond that code are fetched when that code is
   decoded. */
    {
	var a;			// counter for codes of length k
	var c = new Array(this.BMAX+1);	// bit length count table
	var el;			// length of EOB code (value 256)
	var f;			// i repeats in table every f entries
	var g;			// maximum code length
	var h;			// table level
	var i;			// counter, current code
	var j;			// counter
	var k;			// number of bits in current code
	var lx = new Array(this.BMAX+1);	// stack of bits per table
	var p;			// pointer into c[], b[], or v[]
	var pidx;		// index of p
	var q;			// (zip_HuftNode) points to current table
	var r = new zip_HuftNode(); // table entry for structure assignment
	var u = new Array(this.BMAX); // zip_HuftNode[BMAX][]  table stack
	var v = new Array(this.N_MAX); // values in order of bit length
	var w;
	var x = new Array(this.BMAX+1);// bit offsets, then code stack
	var xp;			// pointer into x or c
	var y;			// number of dummy codes added
	var z;			// number of entries in current table
	var o;
	var tail;		// (zip_HuftList)

	tail = this.root = null;
	for(i = 0; i < c.length; i++)
	    c[i] = 0;
	for(i = 0; i < lx.length; i++)
	    lx[i] = 0;
	for(i = 0; i < u.length; i++)
	    u[i] = null;
	for(i = 0; i < v.length; i++)
	    v[i] = 0;
	for(i = 0; i < x.length; i++)
	    x[i] = 0;

	// Generate counts for each bit length
	el = n > 256 ? b[256] : this.BMAX; // set length of EOB code, if any
	p = b; pidx = 0;
	i = n;
	do {
	    c[p[pidx]]++;	// assume all entries <= BMAX
	    pidx++;
	} while(--i > 0);
	if(c[0] == n) {	// null input--all zero length codes
	    this.root = null;
	    this.m = 0;
	    this.status = 0;
	    return;
	}

	// Find minimum and maximum length, bound *m by those
	for(j = 1; j <= this.BMAX; j++)
	    if(c[j] != 0)
		break;
	k = j;			// minimum code length
	if(mm < j)
	    mm = j;
	for(i = this.BMAX; i != 0; i--)
	    if(c[i] != 0)
		break;
	g = i;			// maximum code length
	if(mm > i)
	    mm = i;

	// Adjust last length count to fill out codes, if needed
	for(y = 1 << j; j < i; j++, y <<= 1)
	    if((y -= c[j]) < 0) {
		this.status = 2;	// bad input: more codes than bits
		this.m = mm;
		return;
	    }
	if((y -= c[i]) < 0) {
	    this.status = 2;
	    this.m = mm;
	    return;
	}
	c[i] += y;

	// Generate starting offsets into the value table for each length
	x[1] = j = 0;
	p = c;
	pidx = 1;
	xp = 2;
	while(--i > 0)		// note that i == g from above
	    x[xp++] = (j += p[pidx++]);

	// Make a table of values in order of bit lengths
	p = b; pidx = 0;
	i = 0;
	do {
	    if((j = p[pidx++]) != 0)
		v[x[j]++] = i;
	} while(++i < n);
	n = x[g];			// set n to length of v

	// Generate the Huffman codes and for each, make the table entries
	x[0] = i = 0;		// first Huffman code is zero
	p = v; pidx = 0;		// grab values in bit order
	h = -1;			// no tables yet--level -1
	w = lx[0] = 0;		// no bits decoded yet
	q = null;			// ditto
	z = 0;			// ditto

	// go through the bit lengths (k already is bits in shortest code)
	for(; k <= g; k++) {
	    a = c[k];
	    while(a-- > 0) {
		// here i is the Huffman code of length k bits for value p[pidx]
		// make tables up to required level
		while(k > w + lx[1 + h]) {
		    w += lx[1 + h]; // add bits already decoded
		    h++;

		    // compute minimum size table less than or equal to *m bits
		    z = (z = g - w) > mm ? mm : z; // upper limit
		    if((f = 1 << (j = k - w)) > a + 1) { // try a k-w bit table
			// too few codes for k-w bit table
			f -= a + 1;	// deduct codes from patterns left
			xp = k;
			while(++j < z) { // try smaller tables up to z bits
			    if((f <<= 1) <= c[++xp])
				break;	// enough codes to use up j bits
			    f -= c[xp];	// else deduct codes from patterns
			}
		    }
		    if(w + j > el && w < el)
			j = el - w;	// make EOB code end at table
		    z = 1 << j;	// table entries for j-bit table
		    lx[1 + h] = j; // set table size in stack

		    // allocate and link in new table
		    q = new Array(z);
		    for(o = 0; o < z; o++) {
			q[o] = new zip_HuftNode();
		    }

		    if(tail == null)
			tail = this.root = new zip_HuftList();
		    else
			tail = tail.next = new zip_HuftList();
		    tail.next = null;
		    tail.list = q;
		    u[h] = q;	// table starts after link

		    /* connect to last table, if there is one */
		    if(h > 0) {
			x[h] = i;		// save pattern for backing up
			r.b = lx[h];	// bits to dump before this table
			r.e = 16 + j;	// bits in this table
			r.t = q;		// pointer to this table
			j = (i & ((1 << w) - 1)) >> (w - lx[h]);
			u[h-1][j].e = r.e;
			u[h-1][j].b = r.b;
			u[h-1][j].n = r.n;
			u[h-1][j].t = r.t;
		    }
		}

		// set up table entry in r
		r.b = k - w;
		if(pidx >= n)
		    r.e = 99;		// out of values--invalid code
		else if(p[pidx] < s) {
		    r.e = (p[pidx] < 256 ? 16 : 15); // 256 is end-of-block code
		    r.n = p[pidx++];	// simple code is just the value
		} else {
		    r.e = e[p[pidx] - s];	// non-simple--look up in lists
		    r.n = d[p[pidx++] - s];
		}

		// fill code-like entries with r //
		f = 1 << (k - w);
		for(j = i >> w; j < z; j += f) {
		    q[j].e = r.e;
		    q[j].b = r.b;
		    q[j].n = r.n;
		    q[j].t = r.t;
		}

		// backwards increment the k-bit code i
		for(j = 1 << (k - 1); (i & j) != 0; j >>= 1)
		    i ^= j;
		i ^= j;

		// backup over finished tables
		while((i & ((1 << w) - 1)) != x[h]) {
		    w -= lx[h];		// don't need to update q
		    h--;
		}
	    }
	}

	/* return actual size of base table */
	this.m = lx[1];

	/* Return true (1) if we were given an incomplete table */
	this.status = ((y != 0 && g != 1) ? 1 : 0);
    } /* end of constructor */
}


/* routines (inflate) */

var zip_GET_BYTE = function() {
    if(zip_inflate_data.length == zip_inflate_pos)
	return -1;
    return zip_inflate_data.charCodeAt(zip_inflate_pos++) & 0xff;
}

var zip_NEEDBITS = function(n) {
    while(zip_bit_len < n) {
	zip_bit_buf |= zip_GET_BYTE() << zip_bit_len;
	zip_bit_len += 8;
    }
}

var zip_GETBITS = function(n) {
    return zip_bit_buf & zip_MASK_BITS[n];
}

var zip_DUMPBITS = function(n) {
    zip_bit_buf >>= n;
    zip_bit_len -= n;
}

var zip_inflate_codes = function(buff, off, size) {
    /* inflate (decompress) the codes in a deflated (compressed) block.
       Return an error code or zero if it all goes ok. */
    var e;		// table entry flag/number of extra bits
    var t;		// (zip_HuftNode) pointer to table entry
    var n;

    if(size == 0)
      return 0;

    // inflate the coded data
    n = 0;
    for(;;) {			// do until end of block
	zip_NEEDBITS(zip_bl);
	t = zip_tl.list[zip_GETBITS(zip_bl)];
	e = t.e;
	while(e > 16) {
	    if(e == 99)
		return -1;
	    zip_DUMPBITS(t.b);
	    e -= 16;
	    zip_NEEDBITS(e);
	    t = t.t[zip_GETBITS(e)];
	    e = t.e;
	}
	zip_DUMPBITS(t.b);

	if(e == 16) {		// then it's a literal
	    zip_wp &= zip_WSIZE - 1;
	    buff[off + n++] = zip_slide[zip_wp++] = t.n;
	    if(n == size)
		return size;
	    continue;
	}

	// exit if end of block
	if(e == 15)
	    break;

	// it's an EOB or a length

	// get length of block to copy
	zip_NEEDBITS(e);
	zip_copy_leng = t.n + zip_GETBITS(e);
	zip_DUMPBITS(e);

	// decode distance of block to copy
	zip_NEEDBITS(zip_bd);
	t = zip_td.list[zip_GETBITS(zip_bd)];
	e = t.e;

	while(e > 16) {
	    if(e == 99)
		return -1;
	    zip_DUMPBITS(t.b);
	    e -= 16;
	    zip_NEEDBITS(e);
	    t = t.t[zip_GETBITS(e)];
	    e = t.e;
	}
	zip_DUMPBITS(t.b);
	zip_NEEDBITS(e);
	zip_copy_dist = zip_wp - t.n - zip_GETBITS(e);
	zip_DUMPBITS(e);

	// do the copy
	while(zip_copy_leng > 0 && n < size) {
	    zip_copy_leng--;
	    zip_copy_dist &= zip_WSIZE - 1;
	    zip_wp &= zip_WSIZE - 1;
	    buff[off + n++] = zip_slide[zip_wp++]
		= zip_slide[zip_copy_dist++];
	}

	if(n == size)
	    return size;
    }

    zip_method = -1; // done
    return n;
}

var zip_inflate_stored = function(buff, off, size) {
    /* "decompress" an inflated type 0 (stored) block. */
    var n;

    // go to byte boundary
    n = zip_bit_len & 7;
    zip_DUMPBITS(n);

    // get the length and its complement
    zip_NEEDBITS(16);
    n = zip_GETBITS(16);
    zip_DUMPBITS(16);
    zip_NEEDBITS(16);
    if(n != ((~zip_bit_buf) & 0xffff))
	return -1;			// error in compressed data
    zip_DUMPBITS(16);

    // read and output the compressed data
    zip_copy_leng = n;

    n = 0;
    while(zip_copy_leng > 0 && n < size) {
	zip_copy_leng--;
	zip_wp &= zip_WSIZE - 1;
	zip_NEEDBITS(8);
	buff[off + n++] = zip_slide[zip_wp++] =
	    zip_GETBITS(8);
	zip_DUMPBITS(8);
    }

    if(zip_copy_leng == 0)
      zip_method = -1; // done
    return n;
}

var zip_inflate_fixed = function(buff, off, size) {
    /* decompress an inflated type 1 (fixed Huffman codes) block.  We should
       either replace this with a custom decoder, or at least precompute the
       Huffman tables. */

    // if first time, set up tables for fixed blocks
    if(zip_fixed_tl == null) {
	var i;			// temporary variable
	var l = new Array(288);	// length list for huft_build
	var h;	// zip_HuftBuild

	// literal table
	for(i = 0; i < 144; i++)
	    l[i] = 8;
	for(; i < 256; i++)
	    l[i] = 9;
	for(; i < 280; i++)
	    l[i] = 7;
	for(; i < 288; i++)	// make a complete, but wrong code set
	    l[i] = 8;
	zip_fixed_bl = 7;

	h = new zip_HuftBuild(l, 288, 257, zip_cplens, zip_cplext,
			      zip_fixed_bl);
	if(h.status != 0) {
	    alert("HufBuild error: "+h.status);
	    return -1;
	}
	zip_fixed_tl = h.root;
	zip_fixed_bl = h.m;

	// distance table
	for(i = 0; i < 30; i++)	// make an incomplete code set
	    l[i] = 5;
	zip_fixed_bd = 5;

	h = new zip_HuftBuild(l, 30, 0, zip_cpdist, zip_cpdext, zip_fixed_bd);
	if(h.status > 1) {
	    zip_fixed_tl = null;
	    alert("HufBuild error: "+h.status);
	    return -1;
	}
	zip_fixed_td = h.root;
	zip_fixed_bd = h.m;
    }

    zip_tl = zip_fixed_tl;
    zip_td = zip_fixed_td;
    zip_bl = zip_fixed_bl;
    zip_bd = zip_fixed_bd;
    return zip_inflate_codes(buff, off, size);
}

var zip_inflate_dynamic = function(buff, off, size) {
    // decompress an inflated type 2 (dynamic Huffman codes) block.
    var i;		// temporary variables
    var j;
    var l;		// last length
    var n;		// number of lengths to get
    var t;		// (zip_HuftNode) literal/length code table
    var nb;		// number of bit length codes
    var nl;		// number of literal/length codes
    var nd;		// number of distance codes
    var ll = new Array(286+30); // literal/length and distance code lengths
    var h;		// (zip_HuftBuild)

    for(i = 0; i < ll.length; i++)
	ll[i] = 0;

    // read in table lengths
    zip_NEEDBITS(5);
    nl = 257 + zip_GETBITS(5);	// number of literal/length codes
    zip_DUMPBITS(5);
    zip_NEEDBITS(5);
    nd = 1 + zip_GETBITS(5);	// number of distance codes
    zip_DUMPBITS(5);
    zip_NEEDBITS(4);
    nb = 4 + zip_GETBITS(4);	// number of bit length codes
    zip_DUMPBITS(4);
    if(nl > 286 || nd > 30)
      return -1;		// bad lengths

    // read in bit-length-code lengths
    for(j = 0; j < nb; j++)
    {
	zip_NEEDBITS(3);
	ll[zip_border[j]] = zip_GETBITS(3);
	zip_DUMPBITS(3);
    }
    for(; j < 19; j++)
	ll[zip_border[j]] = 0;

    // build decoding table for trees--single level, 7 bit lookup
    zip_bl = 7;
    h = new zip_HuftBuild(ll, 19, 19, null, null, zip_bl);
    if(h.status != 0)
	return -1;	// incomplete code set

    zip_tl = h.root;
    zip_bl = h.m;

    // read in literal and distance code lengths
    n = nl + nd;
    i = l = 0;
    while(i < n) {
	zip_NEEDBITS(zip_bl);
	t = zip_tl.list[zip_GETBITS(zip_bl)];
	j = t.b;
	zip_DUMPBITS(j);
	j = t.n;
	if(j < 16)		// length of code in bits (0..15)
	    ll[i++] = l = j;	// save last length in l
	else if(j == 16) {	// repeat last length 3 to 6 times
	    zip_NEEDBITS(2);
	    j = 3 + zip_GETBITS(2);
	    zip_DUMPBITS(2);
	    if(i + j > n)
		return -1;
	    while(j-- > 0)
		ll[i++] = l;
	} else if(j == 17) {	// 3 to 10 zero length codes
	    zip_NEEDBITS(3);
	    j = 3 + zip_GETBITS(3);
	    zip_DUMPBITS(3);
	    if(i + j > n)
		return -1;
	    while(j-- > 0)
		ll[i++] = 0;
	    l = 0;
	} else {		// j == 18: 11 to 138 zero length codes
	    zip_NEEDBITS(7);
	    j = 11 + zip_GETBITS(7);
	    zip_DUMPBITS(7);
	    if(i + j > n)
		return -1;
	    while(j-- > 0)
		ll[i++] = 0;
	    l = 0;
	}
    }

    // build the decoding tables for literal/length and distance codes
    zip_bl = zip_lbits;
    h = new zip_HuftBuild(ll, nl, 257, zip_cplens, zip_cplext, zip_bl);
    if(zip_bl == 0)	// no literals or lengths
	h.status = 1;
    if(h.status != 0) {
	if(h.status == 1)
	    ;// **incomplete literal tree**
	return -1;		// incomplete code set
    }
    zip_tl = h.root;
    zip_bl = h.m;

    for(i = 0; i < nd; i++)
	ll[i] = ll[i + nl];
    zip_bd = zip_dbits;
    h = new zip_HuftBuild(ll, nd, 0, zip_cpdist, zip_cpdext, zip_bd);
    zip_td = h.root;
    zip_bd = h.m;

    if(zip_bd == 0 && nl > 257) {   // lengths but no distances
	// **incomplete distance tree**
	return -1;
    }

    if(h.status == 1) {
	;// **incomplete distance tree**
    }
    if(h.status != 0)
	return -1;

    // decompress until an end-of-block code
    return zip_inflate_codes(buff, off, size);
}

var zip_inflate_start = function() {
    var i;

    if(zip_slide == null)
	zip_slide = new Array(2 * zip_WSIZE);
    zip_wp = 0;
    zip_bit_buf = 0;
    zip_bit_len = 0;
    zip_method = -1;
    zip_eof = false;
    zip_copy_leng = zip_copy_dist = 0;
    zip_tl = null;
}

var zip_inflate_internal = function(buff, off, size) {
    // decompress an inflated entry
    var n, i;

    n = 0;
    while(n < size) {
	if(zip_eof && zip_method == -1)
	    return n;

	if(zip_copy_leng > 0) {
	    if(zip_method != zip_STORED_BLOCK) {
		// STATIC_TREES or DYN_TREES
		while(zip_copy_leng > 0 && n < size) {
		    zip_copy_leng--;
		    zip_copy_dist &= zip_WSIZE - 1;
		    zip_wp &= zip_WSIZE - 1;
		    buff[off + n++] = zip_slide[zip_wp++] =
			zip_slide[zip_copy_dist++];
		}
	    } else {
		while(zip_copy_leng > 0 && n < size) {
		    zip_copy_leng--;
		    zip_wp &= zip_WSIZE - 1;
		    zip_NEEDBITS(8);
		    buff[off + n++] = zip_slide[zip_wp++] = zip_GETBITS(8);
		    zip_DUMPBITS(8);
		}
		if(zip_copy_leng == 0)
		    zip_method = -1; // done
	    }
	    if(n == size)
		return n;
	}

	if(zip_method == -1) {
	    if(zip_eof)
		break;

	    // read in last block bit
	    zip_NEEDBITS(1);
	    if(zip_GETBITS(1) != 0)
		zip_eof = true;
	    zip_DUMPBITS(1);

	    // read in block type
	    zip_NEEDBITS(2);
	    zip_method = zip_GETBITS(2);
	    zip_DUMPBITS(2);
	    zip_tl = null;
	    zip_copy_leng = 0;
	}

	switch(zip_method) {
	  case 0: // zip_STORED_BLOCK
	    i = zip_inflate_stored(buff, off + n, size - n);
	    break;

	  case 1: // zip_STATIC_TREES
	    if(zip_tl != null)
		i = zip_inflate_codes(buff, off + n, size - n);
	    else
		i = zip_inflate_fixed(buff, off + n, size - n);
	    break;

	  case 2: // zip_DYN_TREES
	    if(zip_tl != null)
		i = zip_inflate_codes(buff, off + n, size - n);
	    else
		i = zip_inflate_dynamic(buff, off + n, size - n);
	    break;

	  default: // error
	    i = -1;
	    break;
	}

	if(i == -1) {
	    if(zip_eof)
		return 0;
	    return -1;
	}
	n += i;
    }
    return n;
}

var zip_inflate = function(str) {
    var i, j;

    zip_inflate_start();
    zip_inflate_data = str;
    zip_inflate_pos = 0;

    var buff = new Array(1024);
    var aout = [];
    while((i = zip_inflate_internal(buff, 0, buff.length)) > 0) {
	var cbuf = new Array(i);
	for(j = 0; j < i; j++){
	    cbuf[j] = String.fromCharCode(buff[j]);
	}
	aout[aout.length] = cbuf.join("");
    }
    zip_inflate_data = null; // G.C.
    return aout.join("");
}

if (! window.RawDeflate) RawDeflate = {};
RawDeflate.inflate = zip_inflate;

})();

/*

Recursive SAX-emulating parser.

Traverses the DOM tree, triggering SAX events as it goes. Pretty minimal
implementation thus far, ignoring all sorts of details but generally relying
on the document to be already parsed and nodes fully formed (with
attributes, etc) by the DOM parser.

This is fully continuation-based, so the parser "pauses" until the
continuation callback is called by the handler.

Create a new parser like so:

   var parser = new Sax.Parser(parentElement, eventHandler);

and start parsing:

   parser.parse();

Events that are sent to the handler:

  start()  - called at the start of the document (no continuation, synchronous).
  finish() - called at the end of the document (no continuation, synchronous).

  startElement(element continuation) - called when a new element is encountered.
  endElement(element, continuation)  - called when an element is closed.

  textNode(textElement, continuation) - called when a text element is encountered.
                                        no endTextNode callback exists, since text
                                        nodes are self-contained.
*/

var Sax = new function () {

  this.Parser = function (node, handler) {

    var descend = function (node, continuation) {
      switch (node.nodeType) {
        case 1:

          var childNodes = node.childNodes;
          var childNodesLength = childNodes.length;

          function buildContinuation(i, nextContinuation) {
            if (i < childNodesLength) {
              // We have more children to traverse.
              descend(childNodes[i], function () { buildContinuation(i+1, nextContinuation); });
            } else {
              // No more children to traverse; continue on.
              handler.endElement(node, function () { nextContinuation(); });
            }
          }

          handler.startElement(node, function () { buildContinuation(0, continuation); });

          break;

        case 3:
          handler.textNode(node, function () { continuation(); });
          break;

        default:
          continuation();
          break;
      }
    };

    this.parse = function () {
      handler.start();
      descend(node, function () {
        handler.finish();
      });
    };
  }
}();

/* Low-rent random-access IO */
var IOBuffer = function (str) {
  this.pos = 0;

  this.length = str.length;

  this.read = function (bytes) {
    var chars = str.substr(this.pos, bytes);
    this.pos += bytes;
    return chars;
  }
  this.readByte = this.read;

  this.eof = function () {
    return (this.pos >= this.length);
  }

  this.seek = function (newPos) {
    this.pos = newPos;
  }

  this.lastIndexOf = function (matchStr) {
    return str.lastIndexOf(matchStr);
  }
};

var Zip = {

  /* Archive. A ZIP File. */
  Archive: function (archive) {

    // Store the raw content of the ZIP
    var io = new IOBuffer(archive);
    var directoryIndex = new Zip.CentralDirectory(io);

    // We store pointers filename -> Entry
    this.entries = directoryIndex.entries;
    this.files   = directoryIndex.files;
  },

  CentralDirectory: function (io) {

    // Save our current position
    var prev_pos = io.pos;

    // Find the end-of-central-directory record.
    var offset = io.lastIndexOf(String.fromCharCode(0x50, 0x4b, 0x05, 0x06));

    // The directory index is at most 18 bytes for the header,
    // and 65536 bytes for the zip comment.
    if (offset < io.length - (18 + 65536)) {
      throw new Error("Invalid Zip File.");
    }

    // Go to the start of the end-of-central directory record, read it,
    // and then unpack it into our header.
    io.seek(offset);
    var header = io.read(22).unpack('VvvvvVVv');

    var signature             = header[0],
        numOfThisDisk         = header[1],
        numOfFirstDisk        = header[2],
        numberOfEntriesOnDisk = header[3],
        numberOfEntries       = header[4],
        centralDirectorySize  = header[5],
        centralDirectoryOffset= header[6],
        commentLength         = header[7];

    // Read the ZIP comment, if any.
    this.zipComment = io.read(commentLength);

    if (!io.eof) {
      throw new Error("Unexpected extra data at the end of the Zip File.");
    }

    if (numberOfEntriesOnDisk != numberOfEntries) {
      throw new Error("Multiple disk ZIP volumes are not supported.");
      // That said, you should be commended for using 5.25" floppies post-2010.
      //
      // and in JavaScript.
      //
      // Whrrrr-grind-grind-tsk-tsk-tsk-tsk-whrrr
    }

    // Since we're responsible for reading the Central Directory, we handle the
    // IO positioning to start and finish.
    io.seek(centralDirectoryOffset);
    var i = numberOfEntries;

    this.entries = [];
    this.files = {};

    while (i--) {
      var entry = new Zip.CentralDirectoryEntry(io);
      this.entries.push(entry);
      this.files[entry.name] = entry;
    }

    // Restore our original position.
    io.seek(prev_pos);
  },

  CentralDirectoryEntry: function (io) {

    var rawHeader = io.read(46);
    header = rawHeader.unpack('VCCvvvvvVVVvvvvvVV');

    var signature              = header[0],
        version                = header[1],
        fsType                 = header[2],
        versionNeededToExtract = header[3],
        gpFlags                = header[4],
        compressionMethod      = header[5],
        lastModTime            = header[6],
        lastModDate            = header[7],
        crc                    = header[8],
        compressedSize         = header[9],
        uncompressedSize       = header[10],
        nameLength             = header[11],
        extraLength            = header[12],
        commentLength          = header[13],
        diskNumberStart        = header[14],
        internalFileAttr       = header[15],
        externalFileAttr       = header[16],
        localHeaderOffset      = header[17],
        extra,
        comment,
        fileContent,
        isDirectory,
        entry;

    if (signature != 0x02014b50) {
      throw new Error("Invalid directory entry.");
    }

    this.name = io.read(nameLength);
    extra = io.read(extraLength);
    this.comment = io.read(commentLength);

    fileContent = (internalFileAttr & 0x01 == 1) ? 'ascii' : 'binary';

    if (internalFileAttr & 0x02 == 1) {
      throw new Error("This library can't handle mainframe ZIP files.");
      // My sincerest apologies if you're running this on VAX or Z/OS and
      // trying to extract a ZIP archive that uses this neglected format.
      // Patches are welcome!
    }

    this.isDirectory = (this.name.charAt(this.name.length-1) == '/');

    entry = new Zip.Entry(io, localHeaderOffset);
    this.content = entry.content;
  },

  /* Entry. A file (or directory) within a ZIP Archive */
  Entry: function (io, startOffset) {

    // Seek to the beginning of this entry.
    var prev_pos = io.pos;
    io.seek(startOffset);

    var rawHeader = io.read(30);
    var header = rawHeader.unpack('VCCvvvvVVVvv');

    var signature         = header[0],
        version           = header[1],
        fstype            = header[2],
        gpFlags           = header[3],
        compressionMethod = header[4],
        lastModTime       = header[5],
        lastModDate       = header[6],
        crc               = header[7],
        compressedSize    = header[8],
        uncompressedSize  = header[9],
        nameLength        = header[10],
        extraLength       = header[11],
        contentOffset     = startOffset + 30 + nameLength + extraLength,
        name,
        extra,
        content;

    // Each file entry begins with 'PK'
    if (signature != 0x04034B50) {
      throw new Error("Invalid entry");
    }

    name = io.read(nameLength);
    extra = io.read(extraLength);

    this.name = name;

    // We don't pre-extract the content until the user asks for it.
    this.content = function () {

      return function () {
        if (content) return content;

        io.seek(contentOffset);
        var compressedData = io.read(compressedSize);

        switch (compressionMethod) {
          case 0:
            content = compressedData;
            break;
          case 8:
            content = RawDeflate.inflate(compressedData);
            break;
          default:
            throw new Error("Unsupported compression method.");
        }

        return content;
      };
    }();

    // Restore our original position.
    io.seek(prev_pos);
  }
};

/* unpack, like the perl/ruby/python/php method.
   VERY primitive implementation, just what I need for ZIP. */
String.prototype.unpack = function(unpack_cmd) {
  var cmds = unpack_cmd.split('');
  var l = cmds.length;

  var pos = 0;
  var ppos = 0;

  var out = [];

  var i = 0;
  while (i < l) {
    switch (cmds[i++]) {
      case "V":
        // Unsigned long (32-bit), little endian.
        var v =( (this.charCodeAt(pos++) & 0xFF)        |
                ((this.charCodeAt(pos++) & 0xFF) <<  8) |
                ((this.charCodeAt(pos++) & 0xFF) << 16) |
                ((this.charCodeAt(pos++) & 0xFF) << 24) );
        if (v < 0) v += 4294967296;
        out.push(v);
        break;
      case "v":
        // Unsigned short (16-bit), little endian.
        out.push( (this.charCodeAt(pos++) & 0xFF) |
                 ((this.charCodeAt(pos++) & 0xFF) << 8));
        break;
      case "C":
        out.push(this.charCodeAt(pos++));
        break;
      case 'c':
        // Signed char (8-bit)
        var char = this.charCodeAt(pos++);
        out.push(-1 * (char & 128) + (char & 127));
        break;
    }
  }

  return out;
};

var ePub = new function () {

  var parser = new DOMParser();

  this.open = function (uri, callback) {
    var client = new XMLHttpRequest();

    client.onreadystatechange = function () {
      if (client.readyState == 4 && client.status == 200) {
        var archive = new Zip.Archive(client.responseText);
        callback(new ePub.Book(archive));
      } else if (client.readyState == 4 && client.status < 400 && client.status > 299) {
        alert('I need to look elsewhere for the book, but I don\'t know how!');
      } else if (client.readyState == 4) {
        alert('There was an error reading the book! I need CORS support to read books from other domains! (result code was ' + client.readyState + '/' + client.status);
      }
    };
    client.overrideMimeType('text/plain; charset=x-user-defined');
    client.open("GET", uri);
    client.send(null);
  };

  this.Book = function (archive) {

    var ocf = new ePub.OCF(archive.files['META-INF/container.xml'].content());
    var opf = new ePub.OPF(ocf.rootFile, archive);

    this.getFile = opf.getFileByName;

    this.title = opf.title;
    this.author = opf.creator;

    this.contents = opf.contents;
    this.contentsByFile = opf.contentsByFile;

    this.toc = opf.toc.contents;
  };

  this.OCF = function (containerXML) {
    var container = parser.parseFromString(containerXML, "application/xml");

    var rootfiles = container.querySelectorAll("rootfile"),
        formats = {};

    // This ignores the presence of multiple alternate formats of the same type.
    var l = rootfiles.length;
    while (l--) {
      formats[rootfiles[l].getAttribute('media-type')] = rootfiles[l].getAttribute('full-path');
    }

    this.alternateFormats = formats;

    // Since the elements were processed in reverse, this is the first one.
    this.rootFile = formats['application/oebps-package+xml'];
  };

  this.OPF = function (rootFile, archive) {

    var opfXML = archive.files[rootFile].content();
    var opf = parser.parseFromString(opfXML, "application/xml");

    var opfPath = rootFile.substr(0, rootFile.lastIndexOf('/'));

    // Get the spine and manifest to make things easier.
    var spine = opf.querySelector('spine');
    var manifest = opf.querySelector('manifest');

    this.getFileByName = function (fileName) {
      var fullPath = [opfPath, fileName].join("/");

      return archive.files[fullPath];
    };

    this.getFileById = function (id) {
      var fileName = manifest.querySelector("[id='" + id + "']").getAttribute('href');
      return this.getFileByName(fileName);
    }

    // Build the contents file. Needs some work.
    var itemrefs = spine.querySelectorAll('itemref');
    var il = itemrefs.length;
    var contents = [];
    var contentsByFile = {};
    while (il--) {
      var id = itemrefs[il].getAttribute('idref');
      var file = this.getFileById(id);
      contents.unshift(file);
      contentsByFile[file.name] = file;
    }

    this.contents = contents;
    this.contentsByFile = contentsByFile;

    // Basic metadata. Needs some work.
    this.title  = opf.querySelector('title').textContent;
    this.creator = opf.querySelector('creator').textContent;

    // Fetch the table of contents. This (i.e., the spec) is really confusing,
    // so it might be wrong. needs more investigation.
    var tocId = spine.getAttribute('toc');
    this.toc = new ePub.NCX(tocId, this);
  };

  this.NCX = function (tocId, opf) {
    var ncxXML = opf.getFileById(tocId).content();
    var ncx = parser.parseFromString(ncxXML, 'application/xml');

    // navmap > navpoint > navlabel > text(), navmap > navpoint > content into an array
    var navpoints = ncx.querySelectorAll('navMap navPoint');
    var contents = [];

    for (var i = 0, l = navpoints.length; i < l; i++) {
      var src = navpoints[i].querySelector('content').getAttribute('src');
      var file = opf.getFileByName(src);
      var content;

      if (!file) {
        content = function () { return "" };
      } else {
        content = function () {
          return decodeURIComponent( escape(file.content()) )
        };
      }

      var point = {
        title:    navpoints[i].querySelector('navLabel text').textContent,
        fileName: file.name,
        content:  content
      }

      if (!file) {
//        console.log("Couldn't find a file named " + src + " for section named " + point.title);
      }

      var pos = navpoints[i].getAttribute('playOrder') - 1;
      contents[navpoints[i].getAttribute('playOrder')-1] = point;
    }

    this.contents = contents;
  };

}();



/*
Copyright (c) 2008 Fred Palmer fred.palmer_at_gmail.com

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/
function StringBuffer()
{
    this.buffer = [];
}

StringBuffer.prototype.append = function append(string)
{
    this.buffer.push(string);
    return this;
};

StringBuffer.prototype.toString = function toString()
{
    return this.buffer.join("");
};

var Base64 =
{
    codex : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

    encode : function (input)
    {
        var output = new StringBuffer();

        var enumerator = new BinaryEncodeEnumerator(input);
        while (enumerator.moveNext())
        {
            var chr1 = enumerator.current;

            enumerator.moveNext();
            var chr2 = enumerator.current;

            enumerator.moveNext();
            var chr3 = enumerator.current;

            var enc1 = chr1 >> 2;
            var enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            var enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            var enc4 = chr3 & 63;

            if (isNaN(chr2))
            {
                enc3 = enc4 = 64;
            }
            else if (isNaN(chr3))
            {
                enc4 = 64;
            }

            output.append(this.codex.charAt(enc1) + this.codex.charAt(enc2) + this.codex.charAt(enc3) + this.codex.charAt(enc4));
        }

        return output.toString();
    },

    decode : function (input)
    {
        var output = new StringBuffer();

        var enumerator = new Base64DecodeEnumerator(input);
        while (enumerator.moveNext())
        {
            var charCode = enumerator.current;

            if (charCode < 128)
                output.append(String.fromCharCode(charCode));
            else if ((charCode > 191) && (charCode < 224))
            {
                enumerator.moveNext();
                var charCode2 = enumerator.current;

                output.append(String.fromCharCode(((charCode & 31) << 6) | (charCode2 & 63)));
            }
            else
            {
                enumerator.moveNext();
                var charCode2 = enumerator.current;

                enumerator.moveNext();
                var charCode3 = enumerator.current;

                output.append(String.fromCharCode(((charCode & 15) << 12) | ((charCode2 & 63) << 6) | (charCode3 & 63)));
            }
        }

        return output.toString();
    }
}


function BinaryEncodeEnumerator(input)
{
    this._input = input;
    this._index = -1;
}

BinaryEncodeEnumerator.prototype =
{
    current: Number.NaN,

    moveNext: function()
    {
        if (this._index >= (this._input.length - 1)) {
            this.current = Number.NaN;
            return false;
        } else {
            var charCode = this._input.charCodeAt(++this._index);

            this.current = charCode;

            return true;
        }
    }
}

function Base64DecodeEnumerator(input)
{
    this._input = input;
    this._index = -1;
    this._buffer = [];
}

Base64DecodeEnumerator.prototype =
{
    current: 64,

    moveNext: function()
    {
        if (this._buffer.length > 0)
        {
            this.current = this._buffer.shift();
            return true;
        }
        else if (this._index >= (this._input.length - 1))
        {
            this.current = 64;
            return false;
        }
        else
        {
            var enc1 = Base64.codex.indexOf(this._input.charAt(++this._index));
            var enc2 = Base64.codex.indexOf(this._input.charAt(++this._index));
            var enc3 = Base64.codex.indexOf(this._input.charAt(++this._index));
            var enc4 = Base64.codex.indexOf(this._input.charAt(++this._index));

            var chr1 = (enc1 << 2) | (enc2 >> 4);
            var chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            var chr3 = ((enc3 & 3) << 6) | enc4;

            this.current = chr1;

            if (enc3 != 64)
                this._buffer.push(chr2);

            if (enc4 != 64)
                this._buffer.push(chr3);

            return true;
        }
    }
};


var pngMagicNum = "\211PNG\r\n\032\n";
var jpgMagicNum = "\377\330";
var gifMagicNum = "GIF8";

var getImageSize = function (imageData) {
  var nextByte = function() {
    return imageData.charCodeAt(pos++);
  }

  if (imageData.substr(0, 8) === pngMagicNum) {

    // PNG. Easy peasy.
    var pos = imageData.indexOf('IHDR') + 4;

    return { width:  (nextByte() << 24) | (nextByte() << 16) |
                     (nextByte() <<  8) | nextByte(),
             height: (nextByte() << 24) | (nextByte() << 16) |
                     (nextByte() <<  8) | nextByte() };

  } else if (imageData.substr(0, 4) === gifMagicNum) {
    pos = 6;

    return { width:  (nextByte() << 8) | nextByte(),
             height: (nextByte() << 8) | nextByte() };

  } else if (imageData.substr(0, 2) === jpgMagicNum) {

    pos = 2;

    var l = imageData.length;
    while (pos < l) {
      if (nextByte() != 0xFF) return;

      var marker = nextByte();
      if (marker == 0xDA) break;

      size = (nextByte() << 8) | nextByte();

      if (marker >= 0xC0 && marker <= 0xCF && !(marker & 0x4) && !(marker & 0x8)) {
        pos += 1;
        return { height:  (nextByte() << 8) | nextByte(),
                 width: (nextByte() << 8) | nextByte() };

      } else {
        pos += size - 2;
      }
    }
  }
};


var Paginator = function (fromNode, toNode, styleContent) {

  var delay = 0;

  var callbacks = {};
  this.addCallback = function (cbk, cbkFunc) {
    if (callbacks[cbk]) {
      callbacks[cbk].push(cbkFunc);
    } else {
      callbacks[cbk] = [cbkFunc];
    }
  };

  var emitCallback = function (cbk, arg) {
    var cbks = callbacks[cbk];

    if (!cbks) return;

    for (var i = 0, l = cbks.length; i < l; i++) {
      cbks[i](arg);
    }

    if (cbk === 'page') {
      // Give the browser some time to react if we've encountered a new page.
      delay = 20;
    }
  }

  // We store realHeight here so that we don't have to fetch it in a loop.
  var realHeight = document.defaultView.
                   getComputedStyle(toNode, null).
                   getPropertyValue('height').
                   replace('px', '');
  var maxScrollHeight = toNode.offsetHeight - realHeight;

  var realScrollHeight = function () {
    return toNode.scrollHeight - maxScrollHeight;
  };

  var nodeHandler = new function() {
    var running = true,
        started = false,
        currentNode = toNode,
        nodeHierarchy = [];

    // This is a helper function to facilitate properly cloning nodes. If
    // the source documents are the same, we can use cloneNode, but if
    // not we need to use importNode.
    var shallowClone = function() {

      var method;

      if (fromNode.ownerDocument === toNode.ownerDocument) {
        return function (node) {
          return node.cloneNode(false);
        }
      } else {
        var targetDocument = toNode.ownerDocument;

        return function (node) {
          return targetDocument.importNode(node, false);
        }
      }

    }();

    var reset = function () {
      toNode.innerHTML = '';
      currentNode = toNode;

      for (var i = 0, l = nodeHierarchy.length; i < l; i++) {
        childNode = shallowClone(nodeHierarchy[i]);
        currentNode.appendChild(childNode);
        currentNode = childNode;
        currentNode.appendChild(document.createTextNode(""));
      }
    };

    this.start = function () {
      // Clear target node, just in case.
      reset();
      emitCallback('start');
    }

    this.finish = function () {
      emitCallback('page', toNode.cloneNode(true));
      emitCallback('finish');
      reset();
    }

    // Handle an opening element, e.g., <div>, <a>, etc.
    this.startElement = function (element, c) {

      // We don't start on the first element, since the semantic here is
      // that we copy *contained* elements, not the container.
      if (!started) {
        started = true;
        return c();
      }

      // First, clone the node to be copied, fill in data URI if necesssary,
      // and append it to our document.
      var newNode = shallowClone(element);

      if (newNode.nodeName === 'IMG' || newNode.nodeName === 'image') {
        emitCallback('image', newNode);

        newNode.style.height = '';
        newNode.style.width  = '';

        var containerWidth = document.defaultView.
                             getComputedStyle(currentNode, null).
                             getPropertyValue('width').
                             replace('px', '');

        var scale = Math.min(containerWidth / newNode.width,
                             realHeight / newNode.height);

        if (scale < 1) {
          newNode.height = newNode.height * scale;
          newNode.width  = newNode.width  * scale;
        }
      }

      currentNode.appendChild(newNode);

      // If we've exceeded our height now, it's potentially due to image(s).
      // Let's try shrinking them a little. If that doesn't work, we can
      // try moving this element to the next page.
      if (realHeight < realScrollHeight()) {
        var imgs = toNode.getElementsByTagName('IMG');

        var origSizes = [],
            l = imgs.length,
            attempts = 0;

        for (var i = 0; i < l; i++) {
          origSizes[i] = [imgs[i].height, imgs[i].width];
        }

        while (attempts++ < 3 && realHeight < realScrollHeight()) {
          for (var i = 0; i < l; i++) {
            imgs[i].height = imgs[i].height * 0.9;
            imgs[i].width = imgs[i].width * 0.9;
          }
        }

        // If it didn't work, reset the image sizes.
        if (realHeight < realScrollHeight()) {
          for (var i = 0, l = origSizes.length; i < l; i++) {
            imgs[i].height = origSizes[i][0];
            imgs[i].width  = origSizes[i][1];
          }
        }
      }

      if (newNode.nodeName === 'IMG' && realHeight < realScrollHeight()) {
        currentNode.removeChild(newNode);

        emitCallback('page', toNode.cloneNode(true));
        reset();

        currentNode.appendChild(newNode);
      }

      // Now, make this node the currentNode so we can append stuff to it,
      // and track it in the nodeHierarchy.
      currentNode = currentNode.lastChild;
      nodeHierarchy.push(currentNode);

      return c();
    }

    this.endElement = function (element, c) {
      currentNode = currentNode.parentNode;
      nodeHierarchy.pop();

      return c();
    }

    this.textNode = function (element, c) {

      var rawHyphenatedText;
      try {
        rawHyphenatedText = Hyphenator.hyphenate(decodeURIComponent(escape(element.textContent)), 'en');
      } catch (e) {
        rawHyphenatedText = Hyphenator.hyphenate(element.textContent, 'en');
      }
      var newTextNode = currentNode.ownerDocument.createTextNode(rawHyphenatedText);

      currentNode.appendChild(newTextNode);

      if (realHeight >= realScrollHeight()) {
        // We're still safe. Call the callback! Continue! Do not dawdle!
        var tmpDelay = delay;
        delay = 0;
        setTimeout(function continueFast () { c(); }, tmpDelay);
        return;
      }
      // That didn't work. Try the slow approach.

      currentNode.removeChild(newTextNode);

      // Add a text node to the end of currentNode if there isn't already one there.
      if (!currentNode.lastChild || currentNode.lastChild.nodeType != 3) {
        currentNode.appendChild(currentNode.ownerDocument.createTextNode(""));
      }

      var textNode = currentNode.lastChild,
          space = '';

      var incomingText;
      try {
        incomingText = Hyphenator.hyphenate(decodeURIComponent(escape(element.textContent)), 'en');
      } catch (e) {
        incomingText = Hyphenator.hyphenate(element.textContent, 'en');
      }

      var l = incomingText.length;

      var fitText = function (start, sliceLength) {

        if (start === l) {
          var tmpDelay = delay;
          delay = 0;

          setTimeout(function continueSlow () { c(); }, tmpDelay);
          return;
        }


        if (sliceLength <= 0) {
	  // If we're here, it means we don't have any more text in the current
	  // set of chunks that will fit on the page. Trigger a new page!

          emitCallback('page', toNode.cloneNode(true));

          incomingText = incomingText.substr(start, l - start);
          l = incomingText.length;

          // reset our destination collector to the current hierarchy.
          reset();

          // Now that we've reset the currentNode (which is prepped with
          // a blank text node, we need to point our text node at that.
          textNode = currentNode.lastChild;

          // finally, start the process again.
          return fitText(start, l);
        }

        // Copy a slice of text into the text node. Hopefully it fits.
        var testText = ((start == 0) ? '' : ' ') + incomingText.substr(start, sliceLength);

        textNode.textContent += testText;

        if (realHeight < realScrollHeight()) {
          // Reset the text and try again with a more conservative sliceLength.
          textNode.textContent = textNode.textContent.substr(0, sliceLength + ((start == 0) ? 0 : 1));
          fitText(start, incomingText.lastIndexOf(' ', Math.floor(sliceLength / 2)));
        } else {
	  // We only get here by overrunning our bbox, so keep looking for the
	  // floor.
          fitText(sliceLength, incomingText.lastIndexOf(' ', Math.floor(sliceLength / 2)));
        }
      }

//      return fitText(0, l);

      var textChunks;
      try {
        textChunks = Hyphenator.hyphenate(decodeURIComponent(escape(element.textContent)), 'en').split(/[\r\n ]/);
      } catch (e) {
        textChunks = element.textContent.split(/[\r\n ]/);
      }

      var l = textChunks.length;
      while (l--) {
        // Copy this chunk into it, and see if we've overrun our bbox.
        var nextChunk = textChunks.shift();
        textNode.textContent += space + nextChunk;
        space = ' ';

        if (realHeight < realScrollHeight()) {
          // Okay, we've over-stepped our boundaries, pull off that last
          // text chunk and trigger the new page callback.
          textNode.textContent = textNode.textContent.substr(0, textNode.textContent.length - nextChunk.length);

          emitCallback('page', toNode.cloneNode(true));

          // Put our next chunk back in the queue to be processed, and
          // reset our destination collector to the current hierarchy.
          textChunks.unshift(nextChunk);
          l++;
          reset();

          // Now that we've reset the currentNode (which is prepped with
          // a blank text node, we need to point our text node at that.
          textNode = currentNode.lastChild;
          space = '';
        }
      }

      var tmpDelay = delay;
      delay = 0;

      setTimeout(function continueSlow () { c(); }, tmpDelay);
    };
  };

  // The actual paginate function. Provided only to allow deferred starts.
  this.paginate = function () {
    new Sax.Parser(fromNode, nodeHandler).parse();
  };
};


/*

This is a holder for sections; each section needs to store an internal
reference to its own pages, since they are loaded internally via the
contentCallback.

While it would be possible to load all of the sections' pages into a single
array, doing so would make it impossible to access each section
independently (or, at least, much more difficult).

*/

var Section = function (contentCallback) {
  var pages = [],
      lastPage = -1;

  this.currPage = 0;

  var self = this;

  // loadCallback - calls the callback that will load and paginate the content
  // for this section.
  //
  // Calls c() twice if the section isn't loaded the first time around.

  var isLoading = false,
      callbackQueue = [];
  this.loadCallback = function (c) {

    if (lastPage == -1 && !isLoading) {
      isLoading = true;

      var finishLoad = function () {
        self.pageCount = pages.length;
        isLoading = false;

        // The first callback is the one that started the loading.
        c(true);

        // Once we've done that, clear out the pending queue.
        while (callbackQueue.length > 0) {
          callbackQueue.shift()(true);
        }
      }

      var addPage = function (page) {
        lastPage += 1;
        pages.push(page);
      }

      contentCallback(addPage, finishLoad);
    } else {
      c(!isLoading);
      callbackQueue.push(c);
    }
  }

  this.isFirstPage = function () {
    return self.currPage == 0;
  };

  this.seekBeginning = function () {
    self.currPage = 0;
  };

  this.rewind = function (n) {
    self.currPage = Math.max(self.currPage - n, 0);
  }

  this.isLastPage = function () {
    // Yeah, this could be more succinct, but this is more obvious.
    if (isLoading) {
      return false;
    } else if (self.currPage < lastPage + 1) {
      return false;
    } else {
      return true;
    }
  };

  // Seek to the end of a section. Waits for the section to fully load.
  //

  this.seekEnd = function (callback) {
    this.loadCallback(function (loaded) {
      if (loaded) {
        self.currPage = lastPage + 1;
        callback(lastPage + 1);
      }
    });
  };

  // Fetch the next page.
  //

  this.nextPage = function (callback) {

    // We ignore if the whole section is loaded here, triggering the callback
    // only when:
    //
    //   - the page is available
    //   - the page is never going to be available

    // loadCallback will fire twice if the section isn't loaded.
    this.loadCallback(function (loaded) {
      if (pages[self.currPage]) {
        // The page we're looking for is present. Go ahead and load it.
        callback(pages[self.currPage++]);
      } else if (loaded === true) {
        // There is no next page. Send null.
        callback(null);
      }
    });
  };

  // Fetch the previous page.
  //

  this.prevPage = function (callback) {

    // Moving forward in the section always waits for the whole section to load
    // so we don't need to do any checks here to see if it is loaded.

    if (self.currPage > 0) {
      callback(pages[--self.currPage]);
    } else {
      callback(null);
    }
  }
};




/*

+PageHandler+ takes a reference to a book, from which it extracts content.

+PageHandler+ takes an array of +displayElement+s, which are document elements
in which we wish to display the book, and paginates according to the size of
the first of those elements (the assumption is that they are all sized
equally).

It then provides methods to move forwards and backwards in a book, loading
sections as necessary so as to prevent pre-loading the entire book (which
can be quite time consuming, and is unnecessary).

Optional arguments may be supplied, including +pageNumbers+ (an array of
elements whose textContent will be set to the current corresponding page
number) and +chapterName+, whose textContent will be set to the current
chapter name, if any.

*/

var PageHandler = function (book, displayElements, pageNumbers, chapterName) {
  var sections = [],
      pageCounts = [0],
      currSection = 0;

  var self = this;

  self.sections = sections;
  self.displayElements = displayElements;
  self.collectorBase = self.displayElements[0];

  this.sectionsByName = {};

  var loadingIndicator;
  var waiting = 0;

  this.setPages = function (dspElements) {
    // We need to reset the page numbering, since n>1 page layouts can have
    // blank pages between chapters.
    if (self.displayElements.length != dspElements.length) {
      pageCounts = [0];
    }

    self.displayElements = dspElements;
  }

  var showLoadingIndicator = function (t) {
    loadingIndicator = setTimeout( function () {
      document.getElementById('spinner').style.display = 'block';
    }, t);
  };

  var hideLoadingIndicator = function () {
    clearTimeout(loadingIndicator);
    document.getElementById('spinner').style.display = 'none';
  };

  var recalculatePageNumbers = function () {
    for (var i = 1, l = sections.length; i < l; i++) {
      if (pageCounts[i] >= 0) {
        continue;
      } else {
        var sectionOffset = sections[i - 1].pageCount + pageCounts[i - 1];
        var roundingCorrection = sections[i - 1].pageCount % self.displayElements.length;
        pageCounts[i] = sectionOffset + roundingCorrection;
      }
    }
  };

  var showPageNumber = function (pageIdx, pageOffset) {

    if (pageNumbers) {
      if (!(pageCounts[currSection] >= 0)) {
        recalculatePageNumbers();
      }

      pageNumbers[pageIdx].textContent = pageCounts[currSection] + pageOffset;
    }
  };

  // TODO: This is a hard-coded hack, should be made modular.
  this.pageRenderer = function () {
    var totalPageCount = pageCounts.length;
    for (var i = pageCounts.length - 1; i >= 0; i--) {
      if (!pageCounts[i]) continue;

      totalPageCount = pageCounts[i];
      break;
    }

    var currPage = pageCounts[currSection] + sections[currSection].currPage;
    if (!currPage) currPage = Math.min(currSection, totalPageCount - 1);
    if (currPage >= totalPageCount) currPage = currPage - 1;

    drawPct(currPage / totalPageCount);
  };

  this.pageDisplayer = function (pageIdx) {

    this.pageRenderer();

    // Expect to get called within 50 ms, or display the loading indicator.
    showLoadingIndicator(50);
    waiting++;

    return function (page) {
      if (page === null) {
        self.displayElements[pageIdx].innerHTML = '';
        showPageNumber(pageIdx, sections[currSection].currPage + 1);
      } else {
        self.displayElements[pageIdx].innerHTML = page.innerHTML;
        showPageNumber(pageIdx, sections[currSection].currPage);
      }

      if (--waiting <= 0) {
        hideLoadingIndicator();
        waiting = 0;
      }
    }
  };

  this.nextPage = function () {

    if (waiting > 0) return;

    // Move to the next section if we're at the end of this one.
    if (sections[currSection].isLastPage()) {
      if (sections.length > currSection + 1) {
        currSection += 1;
        sections[currSection].seekBeginning();
      } else {
        // do nothing.
        return;
      }
    }

    for (var i = 0, l = self.displayElements.length; i < l; i++) {
      sections[currSection].nextPage(self.pageDisplayer(i));
    }
  };

  this.prevPage = function () {

    // Don't go back a page if we're already trying to do a page movement.
    if (waiting > 0) return;

    if (sections[currSection].currPage <= self.displayElements.length) {

      if (currSection > 0) {

        sections[--currSection].seekEnd( function (sectionLength) {
            var blanks = sectionLength % self.displayElements.length;
            sections[currSection].rewind(self.displayElements.length - blanks);
            self.nextPage();
          }
        );
      }
    } else {
      sections[currSection].rewind(self.displayElements.length * 2);
      self.nextPage();
    }
  };

  this.goToSection = function (secName) {
    currSection = this.sectionsByName[secName];
    sections[currSection].seekBeginning();
  };

  /*
   *
   *  The content collector. This uses the first displayElement as a template
   *  to paginate the text. We call it from pageHandler.addSection to generate
   *  callbacks that will return content for us when we need it.
   *
   */

  var contentsLoader = function () {
    var parser = new DOMParser();

    var getNewCollector = function () {
      var contentCollector = self.collectorBase.cloneNode(false);
      contentCollector.id = 'contentCollector';
      contentCollector.style.marginTop = '10000px';
      self.collectorBase.parentNode.appendChild(contentCollector);

      return contentCollector;
    };

    return function (contentChunk) {
      return function (addPageCallback, finishCallback) {
        var contentDoc = parser.parseFromString(contentChunk.content(), 'application/xml'),
            contentHeader = contentDoc.getElementsByTagName('head')[0],
            contentContainer = contentDoc.getElementsByTagName('body')[0];

        var contentCollector = getNewCollector();

        var styleContent;
        for (var i = 0, l = contentHeader.children.length; i < l; i++) {
          var elem = contentHeader.children[i];

          if (elem.nodeName == 'link' && elem.rel == 'stylesheet') {
            if (!styleContent) styleContent = '';
            styleContent += book.getFile(elem.getAttribute('href')).content();
          } else if (elem.nodeName == 'style') {
            if (!styleContent) styleContent = '';
            styleContent += elem.textContent;
          }
        }

        if (!document.getElementById('rePublishStyle')) {
          var ssheet = document.createElement('style');
          ssheet.id = 'rePublishStyle';
          ssheet.textContent = styleContent;
          document.getElementsByTagName('head')[0].appendChild(ssheet);
        } else {
          var ssheet = document.getElementById('rePublishStyle');
          ssheet.textContent = styleContent;
        }

        var paginator = new Paginator(contentContainer, contentCollector, styleContent);

        paginator.addCallback('page', function (page) {
          addPageCallback(page, contentHeader);
        });

        paginator.addCallback('finish', function () {
          contentCollector.parentNode.removeChild(contentCollector);
          finishCallback();
        });

        paginator.addCallback('image', function (image) {
          var img;
          if (image.getAttribute('src')) {
            var img = book.getFile(image.getAttribute('src'));
          } else {
            var img = book.getFile(image.getAttribute('xlink:href'));
          }

          if (!img) return;

          var imgContent = img.content();
          var b64imgContent = Base64.encode(imgContent);

          try {
            var sz = getImageSize(imgContent);
            if (sz) {
              image.width = sz.width;
              image.height = sz.height;
            }
          } catch (e) {
            // console.log('error finding image size for ' + image.getAttribute('src'));
          }

          var imgType = img.name.substr(img.name.lastIndexOf('.') + 1, img.name.length);
          var dataUri = "data:image/" + imgType + ";base64," + b64imgContent;
          image.setAttribute('src', dataUri);
        });

        paginator.paginate();
      };
    };
  }();

  // addSection takes a callback function that will open the section
  this.addSection = function (contentRef) {
    var func = contentsLoader(contentRef);

    var section = new Section(func);
    this.sections.push(section);
    this.sectionsByName[contentRef.name] = sections.length - 1;

    return section;
  };


  // Load the book sections.
  for (var i = 0, l = book.contents.length; i < l; i++) {
    this.addSection(book.contents[i]);
  }

var accum = 0;
var naccum = 0;
  this.display = function () {

    var l = book.contents.length;
    function loadSection (n) {
      if (n < l) {
var startTime = new Date();
        // Load section n, and schedule the next section to load in 100ms.
        sections[n].loadCallback( function (loaded) {
var finishTime = new Date();
console.log("Loading section " + n + " took " + (finishTime - startTime) + "ms");
accum += finishTime - startTime;
naccum++;
          if (loaded) {
            setTimeout( function () { loadSection(n+1) }, 20);
          }
        });
      } else {
        console.log('average: ' + (accum / naccum));
      }
    }

    loadSection(0);
    this.nextPage();
  }
};



/*!
 *  Hyphenator 2.4.0 - client side hyphenation for webbrowsers
 *  Copyright (C) 2009  Mathias Nater, Zürich (mathias at mnn dot ch)
 *  Project and Source hosted on http://code.google.com/p/hyphenator/
 *
 *  This JavaScript code is free software: you can redistribute
 *  it and/or modify it under the terms of the GNU Lesser
 *  General Public License (GNU LGPL) as published by the Free Software
 *  Foundation, either version 3 of the License, or (at your option)
 *  any later version.  The code is distributed WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 *  As additional permission under GNU GPL version 3 section 7, you
 *  may distribute non-source (e.g., minimized or compacted) forms of
 *  that code without the copy of the GNU GPL normally required by
 *  section 4, provided you include this license notice and a URL
 *  through which recipients can access the Corresponding Source.
 */

/*
 *  Comments are jsdoctoolkit formatted. See jsdoctoolkit.org
 */

/* The following comment is for JSLint: */
/*global window, ActiveXObject, unescape */
/*jslint browser: true, eqeqeq: true, immed: true, newcap: true, nomen: true, onevar: true, undef: true, white: true, indent: 4*/

/**
 * @fileOverview
 * A script that does hyphenation in (X)HTML files
 * @author Mathias Nater, <a href = "mailto:mathias@mnn.ch">mathias@mnn.ch</a>
 * @version 2.4.0
  */

/**
 * @constructor
 * @description Provides all functionality to do hyphenation, except the patterns that are loaded
 * externally.
 * @namespace Holds all methods and properties
 * @example
 * &lt;script src = "Hyphenator.js" type = "text/javascript"&gt;&lt;/script&gt;
 * &lt;script type = "text/javascript"&gt;
 *   Hyphenator.run();
 * &lt;/script&gt;
 */
var Hyphenator = (function () {


	/**
	 * @name Hyphenator-languageHint
	 * @fieldOf Hyphenator
	 * @description
	 * A string to be displayed in a prompt if the language can't be guessed.
	 * If you add hyphenation patterns change this string.
	 * Internally, this string is used to define languages that are supported by Hyphenator.
	 * @see Hyphenator-supportedLang
	 * @type string
	 * @private
	 * @see Hyphenator-autoSetMainLanguage
	 */
	var languageHint = 'cs, da, bn, de, en, es, fi, fr, gu, hi, hu, it, kn, ml, nl, or, pa, pl, pt, ru, sv, ta, te, tr, uk',

	/**
	 * @name Hyphenator-supportedLang
	 * @fieldOf Hyphenator
	 * @description
	 * A generated key-value object that stores supported languages.
	 * The languages are retrieved from {@link Hyphenator-languageHint}.
	 * @type object
	 * @private
	 * @example
	 * Check if language lang is supported:
	 * if (supportedLang[lang])
	 */
	supportedLang = (function () {
		var k, i = 0, a = languageHint.split(', '), r = {};
		while (!!(k = a[i++])) {
			r[k] = true;
		}
		return r;
	}()),

	/**
	 * @name Hyphenator-prompterStrings
	 * @fieldOf Hyphenator
	 * @description
	 * A key-value object holding the strings to be displayed if the language can't be guessed
	 * If you add hyphenation patterns change this string.
	 * @type object
	 * @private
	 * @see Hyphenator-autoSetMainLanguage
	 */
	prompterStrings = {
		'cs': 'Jazyk této internetové stránky nebyl automaticky rozpoznán. Určete prosím její jazyk:',
		'da': 'Denne websides sprog kunne ikke bestemmes. Angiv venligst sprog:',
		'de': 'Die Sprache dieser Webseite konnte nicht automatisch bestimmt werden. Bitte Sprache angeben:',
		'en': 'The language of this website could not be determined automatically. Please indicate the main language:',
		'es': 'El idioma del sitio no pudo determinarse autom%E1ticamente. Por favor, indique el idioma principal:',
		'fi': 'Sivun kielt%E4 ei tunnistettu automaattisesti. M%E4%E4rit%E4 sivun p%E4%E4kieli:',
		'fr': 'La langue de ce site n%u2019a pas pu %EAtre d%E9termin%E9e automatiquement. Veuillez indiquer une langue, s.v.p.%A0:',
		'hu': 'A weboldal nyelvét nem sikerült automatikusan megállapítani. Kérem adja meg a nyelvet:',
		'it': 'Lingua del sito sconosciuta. Indicare una lingua, per favore:',
		'ml': 'ഈ വെ%u0D2C%u0D4D%u200Cസൈറ്റിന്റെ ഭാഷ കണ്ടുപിടിയ്ക്കാ%u0D28%u0D4D%u200D കഴിഞ്ഞില്ല. ഭാഷ ഏതാണെന്നു തിരഞ്ഞെടുക്കുക:',
		'nl': 'De taal van deze website kan niet automatisch worden bepaald. Geef de hoofdtaal op:',
		'pt': 'A língua deste site não pôde ser determinada automaticamente. Por favor indique a língua principal:',
		'ru': 'Язык этого сайта не может быть определен автоматически. Пожалуйста укажите язык:',
		'sv': 'Spr%E5ket p%E5 den h%E4r webbplatsen kunde inte avg%F6ras automatiskt. V%E4nligen ange:',
		'tr': 'Bu web sitesinin dilini otomatik olarak tespit edilememiştir. Lütfen ana dili gösterir:',
		'uk': 'Мова цього веб-сайту не може бути визначена автоматично. Будь ласка, вкажіть головну мову:'
	},

	/**
	 * @name Hyphenator-basePath
	 * @fieldOf Hyphenator
	 * @description
	 * A string storing the basepath from where Hyphenator.js was loaded.
	 * This is used to load the patternfiles.
	 * The basepath is determined dynamically by searching all script-tags for Hyphenator.js
	 * If the path cannot be determined http://hyphenator.googlecode.com/svn/trunk/ is used as fallback.
	 * @type string
	 * @private
	 * @see Hyphenator-loadPatterns
	 */
	basePath = (function () {
		var s = document.getElementsByTagName('script'), i = 0, p, src, t;
		while (!!(t = s[i++])) {
			if (!t.src) {
				continue;
			}
			src = t.src;
			p = src.indexOf('Hyphenator.js');
			if (p !== -1) {
				return src.substring(0, p);
			}
		}
		return 'http://hyphenator.googlecode.com/svn/trunk/';
	}()),

	/**
	 * @name Hyphenator-isLocal
	 * @fieldOf Hyphenator
	 * @description
	 * isLocal is true, if Hyphenator is loaded from the same domain, as the webpage, but false, if
	 * it's loaded from an external source (i.e. directly from google.code)
	 */
	isLocal = (function () {
		var re = false;
		if (window.location.href.indexOf(basePath) !== -1) {
			re = true;
		}
		return re;
	}()),

	/**
	 * @name Hyphenator-documentLoaded
	 * @fieldOf Hyphenator
	 * @description
	 * documentLoaded is true, when the DOM has been loaded. This is set by runOnContentLoaded
	 */
	documentLoaded = false,

	/**
	 * @name Hyphenator-dontHyphenate
	 * @fieldOf Hyphenator
	 * @description
	 * A key-value object containing all html-tags whose content should not be hyphenated
	 * @type object
	 * @private
	 * @see Hyphenator-hyphenateElement
	 */
	dontHyphenate = {'script': true, 'code': true, 'pre': true, 'img': true, 'br': true, 'samp': true, 'kbd': true, 'var': true, 'abbr': true, 'acronym': true, 'sub': true, 'sup': true, 'button': true, 'option': true, 'label': true, 'textarea': true},

	/**
	 * @name Hyphenator-enableCache
	 * @fieldOf Hyphenator
	 * @description
	 * A variable to set if caching is enabled or not
	 * @type boolean
	 * @default true
	 * @private
	 * @see Hyphenator.config
	 * @see hyphenateWord
	 */
	enableCache = true,

	/**
	 * @name Hyphenator-enableRemoteLoading
	 * @fieldOf Hyphenator
	 * @description
	 * A variable to set if pattern files should be loaded remotely or not
	 * @type boolean
	 * @default true
	 * @private
	 * @see Hyphenator.config
	 * @see Hyphenator-loadPatterns
	 */
	enableRemoteLoading = true,

	/**
	 * @name Hyphenator-displayToggleBox
	 * @fieldOf Hyphenator
	 * @description
	 * A variable to set if the togglebox should be displayed or not
	 * @type boolean
	 * @default false
	 * @private
	 * @see Hyphenator.config
	 * @see Hyphenator-toggleBox
	 */
	displayToggleBox = false,

	/**
	 * @name Hyphenator-hyphenateClass
	 * @fieldOf Hyphenator
	 * @description
	 * A string containing the css-class-name for the hyphenate class
	 * @type string
	 * @default 'hyphenate'
	 * @private
	 * @example
	 * &lt;p class = "hyphenate"&gt;Text&lt;/p&gt;
	 * @see Hyphenator.config
	 */
	hyphenateClass = 'hyphenate',

	/**
	 * @name Hyphenator-dontHyphenateClass
	 * @fieldOf Hyphenator
	 * @description
	 * A string containing the css-class-name for elements that should not be hyphenated
	 * @type string
	 * @default 'donthyphenate'
	 * @private
	 * @example
	 * &lt;p class = "donthyphenate"&gt;Text&lt;/p&gt;
	 * @see Hyphenator.config
	 */
	dontHyphenateClass = 'donthyphenate',

	/**
	 * @name Hyphenator-min
	 * @fieldOf Hyphenator
	 * @description
	 * A number wich indicates the minimal length of words to hyphenate.
	 * @type number
	 * @default 6
	 * @private
	 * @see Hyphenator.config
	 */
	min = 6,

	/**
	 * @name Hyphenator-isBookmarklet
	 * @fieldOf Hyphenator
	 * @description
	 * Indicates if Hyphanetor runs as bookmarklet or not.
	 * @type boolean
	 * @default false
	 * @private
	 */
	isBookmarklet = (function () {
		var loc = null, re = false, jsArray = document.getElementsByTagName('script'), i, l;
		for (i = 0, l = jsArray.length; i < l; i++) {
			if (!!jsArray[i].getAttribute('src')) {
				loc = jsArray[i].getAttribute('src');
			}
			if (!loc) {
				continue;
			} else if (loc.indexOf('Hyphenator.js?bm=true') !== -1) {
				re = true;
			}
		}
		return re;
	}()),

	/**
	 * @name Hyphenator-mainLanguage
	 * @fieldOf Hyphenator
	 * @description
	 * The general language of the document
	 * @type number
	 * @private
	 * @see Hyphenator-autoSetMainLanguage
	 */
	mainLanguage = null,

	/**
	 * @name Hyphenator-elements
	 * @fieldOf Hyphenator
	 * @description
	 * An array holding all elements that have to be hyphenated. This var is filled by
	 * {@link Hyphenator-gatherDocumentInfos}
	 * @type array
	 * @private
	 */
	elements = [],

	/**
	 * @name Hyphenator-exceptions
	 * @fieldOf Hyphenator
	 * @description
	 * An object containing exceptions as comma separated strings for each language.
	 * When the language-objects are loaded, their exceptions are processed, copied here and then deleted.
	 * @see Hyphenator-prepareLanguagesObj
	 * @type object
	 * @private
	 */
	exceptions = {},

	/**
	 * @name Hyphenator-docLanguages
	 * @fieldOf Hyphenator
	 * @description
	 * An object holding all languages used in the document. This is filled by
	 * {@link Hyphenator-gatherDocumentInfos}
	 * @type object
	 * @private
	 */
	docLanguages = {},


	/**
	 * @name Hyphenator-state
	 * @fieldOf Hyphenator
	 * @description
	 * A number that inidcates the current state of the script
	 * 0: not initialized
	 * 1: loading patterns
	 * 2: ready
	 * 3: hyphenation done
	 * 4: hyphenation removed
	 * @type number
	 * @private
	 */
	state = 0,

	/**
	 * @name Hyphenator-url
	 * @fieldOf Hyphenator
	 * @description
	 * A string containing a RegularExpression to match URL's
	 * @type string
	 * @private
	 */
	url = '(\\w*:\/\/)?((\\w*:)?(\\w*)@)?((([\\d]{1,3}\\.){3}([\\d]{1,3}))|(([\\w]*\\.)+([\\w]{2,4})))(:\\d*)?(\/[\\w#!:\\.?\\+=&%@!\\-]*)*',

	/**
	 * @name Hyphenator-mail
	 * @fieldOf Hyphenator
	 * @description
	 * A string containing a RegularExpression to match mail-adresses
	 * @type string
	 * @private
	 */
	mail = '[\\w-\\.]+@[\\w\\.]+',

	/**
	 * @name Hyphenator-urlRE
	 * @fieldOf Hyphenator
	 * @description
	 * A RegularExpressions-Object for url- and mail adress matching
	 * @type object
	 * @private
	 */
	urlOrMailRE = new RegExp('(' + url + ')|(' + mail + ')', 'i'),

	/**
	 * @name Hyphenator-zeroWidthSpace
	 * @fieldOf Hyphenator
	 * @description
	 * A string that holds a char.
	 * Depending on the browser, this is the zero with space or an empty string.
	 * The zeroWidthSpace is inserted after a '-' in compound words, so even FF and IE
	 * will break after a '-' if necessary.
	 * zeroWidthSpace is also used to break URLs
	 * @type string
	 * @private
	 */
	zeroWidthSpace = (function () {
		var zws, ua = navigator.userAgent.toLowerCase();
		if (ua.indexOf('msie 6') === -1) {
			zws = String.fromCharCode(8203); //Unicode zero width space
		} else {
			zws = ''; //IE6 doesn't support zws
		}
		return zws;
	}()),

	/**
	 * @name Hyphenator-onHyphenationDone
	 * @fieldOf Hyphenator
	 * @description
	 * A method to be called, when the last element has been hyphenated or the hyphenation has been
	 * removed from the last element.
	 * @see Hyphenator.config
	 * @type function
	 * @private
	 */
	onHyphenationDone = function () {},

	/**
	 * @name Hyphenator-onError
	 * @fieldOf Hyphenator
	 * @description
	 * A function that can be called upon an error.
	 * @see Hyphenator.config
	 * @type function
	 * @private
	 */
	onError = function (e) {
		alert("Hyphenator.js says:\n\nAn Error ocurred:\n" + e.message);
	},

	/**
	 * @name Hyphenator-selectorFunction
	 * @fieldOf Hyphenator
	 * @description
	 * A function that has to return a HTMLNodeList of Elements to be hyphenated.
	 * By default it uses the classname ('hyphenate') to select the elements.
	 * @see Hyphenator.config
	 * @type function
	 * @private
	 */
	selectorFunction = function () {
		var tmp, el = [], i, l;
		if (document.getElementsByClassName) {
			el = document.getElementsByClassName(hyphenateClass);
		} else {
			tmp = document.getElementsByTagName('*');
			l = tmp.length;
			for (i = 0; i < l; i++)
			{
				if (tmp[i].className.indexOf(hyphenateClass) !== -1 && tmp[i].className.indexOf(dontHyphenateClass) === -1) {
					el.push(tmp[i]);
				}
			}
		}
		return el;
	},

	/**
	 * @name Hyphenator-intermediateState
	 * @fieldOf Hyphenator
	 * @description
	 * The value of style.visibility of the text while it is hyphenated.
	 * @see Hyphenator.config
	 * @type string
	 * @private
	 */
	intermediateState = 'hidden',

	/**
	 * @name Hyphenator-hyphen
	 * @fieldOf Hyphenator
	 * @description
	 * A string containing the character for in-word-hyphenation
	 * @type string
	 * @default the soft hyphen
	 * @private
	 * @see Hyphenator.config
	 */
	hyphen = String.fromCharCode(173),

	/**
	 * @name Hyphenator-urlhyphen
	 * @fieldOf Hyphenator
	 * @description
	 * A string containing the character for url/mail-hyphenation
	 * @type string
	 * @default the zero width space
	 * @private
	 * @see Hyphenator.config
	 * @see Hyphenator-zeroWidthSpace
	 */
	urlhyphen = zeroWidthSpace,

	/**
	 * @name Hyphenator-Expando
	 * @methodOf Hyphenator
	 * @description
	 * This custom object stores data for elements: storing data directly in elements
	 * (DomElement.customData = foobar;) isn't a good idea. It would lead to conflicts
	 * in form elements, when the form has a child with name="foobar". Therefore, this
	 * solution follows the approach of jQuery: the data is stored in an object and
	 * referenced by a unique attribute of the element. The attribute has a name that
	 * is built by the prefix "HyphenatorExpando_" and a random number, so if the very
	 * very rare case occurs, that there's already an attribute with the same name, a
	 * simple reload is enough to make it function.
	 * @private
	 */
	Expando = (function () {
		var container = {},
			name = "HyphenatorExpando_" + Math.random(),
			uuid = 0;
		return {
			getDataForElem : function (elem) {
				return container[elem[name]];
			},
			setDataForElem : function (elem, data) {
				var id;
				if (elem[name] && elem[name] !== '') {
					id = elem[name];
				} else {
					id = uuid++;
					elem[name] = id;
				}
				container[id] = data;
			},
			appendDataForElem : function (elem, data) {
				var k;
				for (k in data) {
					if (data.hasOwnProperty(k)) {
						container[elem[name]][k] = data[k];
					}
				}
			},
			delDataOfElem : function (elem) {
				delete container[elem[name]];
			}
		};
	}()),

	/*
	 * runOnContentLoaded is based od jQuery.bindReady()
	 * see
	 * jQuery JavaScript Library v1.3.2
	 * http://jquery.com/
	 *
	 * Copyright (c) 2009 John Resig
	 * Dual licensed under the MIT and GPL licenses.
	 * http://docs.jquery.com/License
	 *
	 * Date: 2009-02-19 17:34:21 -0500 (Thu, 19 Feb 2009)
	 * Revision: 6246
	 */
	/**
	 * @name Hyphenator-runOnContentLoaded
	 * @methodOf Hyphenator
	 * @description
	 * A crossbrowser solution for the DOMContentLoaded-Event based on jQuery
	 * <a href = "http://jquery.com/</a>
	 * @param object the window-object
	 * @param function-object the function to call onDOMContentLoaded
	 * @private
	 */
	runOnContentLoaded = function (w, f) {
		var oldonload = w.onload;
		if (documentLoaded) {
			f();
			return;
		}
		function init() {
			if (!documentLoaded) {
				documentLoaded = true;
				f();
			}
		}

		// Mozilla, Opera and webkit nightlies currently support this event
		if (document.addEventListener) {
			// Use the handy event callback
			document.addEventListener("DOMContentLoaded", function () {
				document.removeEventListener("DOMContentLoaded", arguments.callee, false);
				init();
			}, false);

		// If IE event model is used
		} else if (document.attachEvent) {
			// ensure firing before onload,
			// maybe late but safe also for iframes
			document.attachEvent("onreadystatechange", function () {
				if (document.readyState === "complete") {
					document.detachEvent("onreadystatechange", arguments.callee);
					init();
				}
			});

			// If IE and not an iframe
			// continually check to see if the document is ready
			if (document.documentElement.doScroll && window == window.top) {
				(function () {
					if (documentLoaded) {
						return;
					}
					try {
						// If IE is used, use the trick by Diego Perini
						// http://javascript.nwbox.com/IEContentLoaded/
						document.documentElement.doScroll("left");
					} catch (error) {
						setTimeout(arguments.callee, 0);
						return;
					}
					// and execute any waiting functions
					f();
				}());
			}
		}
		// A fallback to window.onload, that will always work
		w.onload = function (e) {
			init();
			if (typeof oldonload === 'function') {
				oldonload();
			}
		};
	},



	/**
	 * @name Hyphenator-getLang
	 * @methodOf Hyphenator
	 * @description
	 * Gets the language of an element. If no language is set, it may use the {@link Hyphenator-mainLanguage}.
	 * @param object The first parameter is an DOM-Element-Object
	 * @param boolean The second parameter is a boolean to tell if the function should return the {@link Hyphenator-mainLanguage}
	 * if there's no language found for the element.
	 * @private
	 */
	getLang = function (el, fallback) {
		if (!!el.getAttribute('lang')) {
			return el.getAttribute('lang').substring(0, 2).toLowerCase();
		}
		// The following doesn't work in IE due to a bug when getAttribute('xml:lang') in a table
		/*if (!!el.getAttribute('xml:lang')) {
			return el.getAttribute('xml:lang').substring(0, 2);
		}*/
		//instead, we have to do this (thanks to borgzor):
		try {
			if (!!el.getAttribute('xml:lang')) {
				return el.getAttribute('xml:lang').substring(0, 2).toLowerCase();
			}
		} catch (ex) {}
		if (el.tagName !== 'HTML') {
			return getLang(el.parentNode, true);
		}
		if (fallback) {
			return mainLanguage;
		}
		return null;
	},

	/**
	 * @name Hyphenator-autoSetMainLanguage
	 * @methodOf Hyphenator
	 * @description
	 * Retrieves the language of the document from the DOM.
	 * The function looks in the following places:
	 * <ul>
	 * <li>lang-attribute in the html-tag</li>
	 * <li>&lt;meta http-equiv = "content-language" content = "xy" /&gt;</li>
	 * <li>&lt;meta name = "DC.Language" content = "xy" /&gt;</li>
	 * <li>&lt;meta name = "language" content = "xy" /&gt;</li>
	 * </li>
	 * If nothing can be found a prompt using {@link Hyphenator-languageHint} and {@link Hyphenator-prompterStrings} is displayed.
	 * If the retrieved language is in the object {@link Hyphenator-supportedLang} it is copied to {@link Hyphenator-mainLanguage}
	 * @private
	 */
	autoSetMainLanguage = function () {
		var el = document.getElementsByTagName('html')[0],
			m = document.getElementsByTagName('meta'),
			i, text, lang, e, ul;
		mainLanguage = getLang(el);
		if (!mainLanguage) {
			for (i = 0; i < m.length; i++) {
				//<meta http-equiv = "content-language" content="xy">
				if (!!m[i].getAttribute('http-equiv') && (m[i].getAttribute('http-equiv') === 'content-language')) {
					mainLanguage = m[i].getAttribute('content').substring(0, 2).toLowerCase();
				}
				//<meta name = "DC.Language" content="xy">
				if (!!m[i].getAttribute('name') && (m[i].getAttribute('name') === 'DC.language')) {
					mainLanguage = m[i].getAttribute('content').substring(0, 2).toLowerCase();
				}
				//<meta name = "language" content = "xy">
				if (!!m[i].getAttribute('name') && (m[i].getAttribute('name') === 'language')) {
					mainLanguage = m[i].getAttribute('content').substring(0, 2).toLowerCase();
				}
			}
		}
		if (!mainLanguage) {
			text = '';
			ul = navigator.language ? navigator.language : navigator.userLanguage;
			ul = ul.substring(0, 2);
			if (prompterStrings.hasOwnProperty(ul)) {
				text = prompterStrings[ul];
			} else {
				text = prompterStrings.en;
			}
			text += ' (ISO 639-1)\n\n' + languageHint;
			lang = window.prompt(unescape(text), ul).toLowerCase();
			if (supportedLang[lang]) {
				mainLanguage = lang;
			} else {
				e = new Error('The language "' + lang + '" is not yet supported.');
				throw e;
			}
		}
	},

	/**
	 * @name Hyphenator-gatherDocumentInfos
	 * @methodOf Hyphenator
	 * @description
	 * This method runs through the DOM and executes the process()-function on:
	 * - every node returned by the {@link Hyphenator-selectorFunction}.
	 * The process()-function copies the element to the elements-variable, sets its visibility
	 * to intermediateState, retrieves its language and recursivly descends the DOM-tree until
	 * the child-Nodes aren't of type 1
	 * @private
	 */
	gatherDocumentInfos = function () {
		var elToProcess, tmp, i = 0,
		process = function (el, hide, lang) {
			var n, i = 0, hyphenatorSettings = {};
			if (hide && intermediateState === 'hidden') {
				if (!!el.getAttribute('style')) {
					hyphenatorSettings.hasOwnStyle = true;
				} else {
					hyphenatorSettings.hasOwnStyle = false;
				}
				hyphenatorSettings.isHidden = true;
				el.style.visibility = 'hidden';
			}
			if (el.lang && typeof(el.lang) === 'string') {
				hyphenatorSettings.language = el.lang.toLowerCase(); //copy attribute-lang to internal lang
			} else if (lang) {
				hyphenatorSettings.language = lang.toLowerCase();
			} else {
				hyphenatorSettings.language = getLang(el, true);
			}
			lang = hyphenatorSettings.language;
			if (supportedLang[lang]) {
				docLanguages[lang] = true;
			} else {
				onError(new Error('Language ' + lang + ' is not yet supported.'));
			}
			Expando.setDataForElem(el, hyphenatorSettings);
			elements.push(el);
			while (!!(n = el.childNodes[i++])) {
				if (n.nodeType === 1 && !dontHyphenate[n.nodeName.toLowerCase()] &&
					n.className.indexOf(dontHyphenateClass) === -1 && !(n in elToProcess)) {
					process(n, false, lang);
				}
			}
		};
		if (Hyphenator.isBookmarklet()) {
			elToProcess = document.getElementsByTagName('body')[0];
			process(elToProcess, false, mainLanguage);
		} else {
			elToProcess = selectorFunction();
			while (!!(tmp = elToProcess[i++]))
			{
				process(tmp, true);
			}
		}
		if (!Hyphenator.languages.hasOwnProperty(mainLanguage)) {
			docLanguages[mainLanguage] = true;
		} else if (!Hyphenator.languages[mainLanguage].prepared) {
			docLanguages[mainLanguage] = true;
		}
		if (elements.length > 0) {
			Expando.appendDataForElem(elements[elements.length - 1], {isLast : true});
		}
	},

	/**
	 * @name Hyphenator-convertPatterns
	 * @methodOf Hyphenator
	 * @description
	 * Converts the patterns from string '_a6' to object '_a':'_a6'.
	 * The result is stored in the {@link Hyphenator-patterns}-object.
	 * @private
	 * @param string the language whose patterns shall be converted
	 */
	convertPatterns = function (lang) {
		var plen, anfang, pats, pat, key, tmp = {};
		pats = Hyphenator.languages[lang].patterns;
		for (plen in pats) {
			if (pats.hasOwnProperty(plen)) {
				plen = parseInt(plen, 10);
				anfang = 0;
				while (!!(pat = pats[plen].substr(anfang, plen))) {
					key = pat.replace(/\d/g, '');
					tmp[key] = pat;
					anfang += plen;
				}
			}
		}
		Hyphenator.languages[lang].patterns = tmp;
		Hyphenator.languages[lang].patternsConverted = true;
	},

	/**
	 * @name Hyphenator-convertExceptionsToObject
	 * @methodOf Hyphenator
	 * @description
	 * Converts a list of comma seprated exceptions to an object:
	 * 'Fortran,Hy-phen-a-tion' -> {'Fortran':'Fortran','Hyphenation':'Hy-phen-a-tion'}
	 * @private
	 * @param string a comma separated string of exceptions (without spaces)
	 */
	convertExceptionsToObject = function (exc) {
		var w = exc.split(', '),
			r = {},
			i, l, key;
		for (i = 0, l = w.length; i < l; i++) {
			key = w[i].replace(/-/g, '');
			if (!r.hasOwnProperty(key)) {
				r[key] = w[i];
			}
		}
		return r;
	},

	/**
	 * @name Hyphenator-loadPatterns
	 * @methodOf Hyphenator
	 * @description
	 * Adds a &lt;script&gt;-Tag to the DOM to load an externeal .js-file containing patterns and settings for the given language.
	 * If the iven language is not in the {@link Hyphenator-supportedLang}-Object it returns.
	 * One may ask why we are not using AJAX to load the patterns. The XMLHttpRequest-Object
	 * has a same-origin-policy. This makes the isBookmarklet-functionality impossible.
	 * @param string The language to load the patterns for
	 * @private
	 * @see Hyphenator-basePath
	 */
	loadPatterns = function (lang) {
		var url, xhr, head, script;
		if (supportedLang[lang] && !Hyphenator.languages[lang]) {
	        url = basePath + 'patterns/' + lang + '.js';
		} else {
			return;
		}
		if (isLocal && !isBookmarklet) {
			//check if 'url' is available:
			xhr = null;
			if (typeof XMLHttpRequest !== 'undefined') {
				xhr = new XMLHttpRequest();
			}
			if (!xhr) {
				try {
					xhr  = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					xhr  = null;
				}
			}
			if (xhr) {
				xhr.open('HEAD', url, false);
				xhr.setRequestHeader('Cache-Control', 'no-cache');
				xhr.send(null);
				if (xhr.status === 404) {
					onError(new Error('Could not load\n' + url));
					delete docLanguages[lang];
					return;
				}
			}
		}
		if (document.createElement) {
			head = document.getElementsByTagName('head').item(0);
			script = document.createElement('script');
			script.src = url;
			script.type = 'text/javascript';
			head.appendChild(script);
		}
	},

	/**
	 * @name Hyphenator-prepareLanguagesObj
	 * @methodOf Hyphenator
	 * @description
	 * Adds a cache to each language and converts the exceptions-list to an object.
	 * @private
	 * @param string the language ob the lang-obj
	 */
	prepareLanguagesObj = function (lang) {
		var lo = Hyphenator.languages[lang], wrd;
		if (!lo.prepared) {
			if (enableCache) {
				lo.cache = {};
			}
			if (lo.hasOwnProperty('exceptions')) {
				Hyphenator.addExceptions(lang, lo.exceptions);
				delete lo.exceptions;
			}
			if (exceptions.hasOwnProperty('global')) {
				if (exceptions.hasOwnProperty(lang)) {
					exceptions[lang] += ', ' + exceptions.global;
				} else {
					exceptions[lang] = exceptions.global;
				}
			}
			if (exceptions.hasOwnProperty(lang)) {
				lo.exceptions = convertExceptionsToObject(exceptions[lang]);
				delete exceptions[lang];
			} else {
				lo.exceptions = {};
			}
			convertPatterns(lang);
			wrd = '[\\w' + lo.specialChars + '@' + String.fromCharCode(173) + '-]{' + min + ',}';
			lo.genRegExp = new RegExp('(' + url + ')|(' + mail + ')|(' + wrd + ')', 'gi');
			lo.prepared = true;
		}
	},

	/**
	 * @name Hyphenator-prepare
	 * @methodOf Hyphenator
	 * @description
	 * This funtion prepares the Hyphenator-Object: If RemoteLoading is turned off, it assumes
	 * that the patternfiles are loaded, all conversions are made and the callback is called.
	 * If RemoteLoading is on (default), it loads the pattern files and waits until they are loaded,
	 * by repeatedly checking Hyphenator.languages. If a patterfile is loaded the patterns are
	 * converted to their object style and the lang-object extended.
	 * Finally the callback is called.
	 * @param function-object callback to call, when all patterns are loaded
	 * @private
	 */
	prepare = function (callback) {
		var lang, docLangEmpty = true, interval;
		if (!enableRemoteLoading) {
			for (lang in Hyphenator.languages) {
				if (Hyphenator.languages.hasOwnProperty(lang)) {
					prepareLanguagesObj(lang);
				}
			}
			state = 2;
			callback();
			return;
		}
		// get all languages that are used and preload the patterns
		state = 1;
		for (lang in docLanguages) {
			if (docLanguages.hasOwnProperty(lang)) {
				loadPatterns(lang);
				docLangEmpty = false;
			}
		}
		if (docLangEmpty) {
			state = 2;
			callback();
			return;
		}
		// wait until they are loaded
		interval = window.setInterval(function () {
			var finishedLoading = false, lang;
			for (lang in docLanguages) {
				if (docLanguages.hasOwnProperty(lang)) {
					if (!Hyphenator.languages[lang]) {
						finishedLoading = false;
						break;
					} else {
						finishedLoading = true;
						delete docLanguages[lang];
						//do conversion while other patterns are loading:
						prepareLanguagesObj(lang);
					}
				}
			}
			if (finishedLoading) {
				window.clearInterval(interval);
				state = 2;
				callback();
			}
		}, 100);
	},

	/**
	 * @name Hyphenator-switchToggleBox
	 * @methodOf Hyphenator
	 * @description
	 * Creates or hides the toggleBox: a small button to turn off/on hyphenation on a page.
	 * @param boolean true when hyphenation is on, false when it's off
	 * @see Hyphenator.config
	 * @private
	 */
	toggleBox = function (s) {
		var myBox, bdy, myIdAttribute, myTextNode, myClassAttribute;
		if (!!(myBox = document.getElementById('HyphenatorToggleBox'))) {
			if (s) {
				myBox.firstChild.data = 'Hy-phe-na-ti-on';
			} else {
				myBox.firstChild.data = 'Hyphenation';
			}
		} else {
			bdy = document.getElementsByTagName('body')[0];
			myBox = document.createElement('div');
			myIdAttribute = document.createAttribute('id');
			myIdAttribute.nodeValue = 'HyphenatorToggleBox';
			myClassAttribute = document.createAttribute('class');
			myClassAttribute.nodeValue = dontHyphenateClass;
			myTextNode = document.createTextNode('Hy-phe-na-ti-on');
			myBox.appendChild(myTextNode);
			myBox.setAttributeNode(myIdAttribute);
			myBox.setAttributeNode(myClassAttribute);
			myBox.onclick =  Hyphenator.toggleHyphenation;
			myBox.style.position = 'absolute';
			myBox.style.top = '0px';
			myBox.style.right = '0px';
			myBox.style.margin = '0';
			myBox.style.backgroundColor = '#AAAAAA';
			myBox.style.color = '#FFFFFF';
			myBox.style.font = '6pt Arial';
			myBox.style.letterSpacing = '0.2em';
			myBox.style.padding = '3px';
			myBox.style.cursor = 'pointer';
			myBox.style.WebkitBorderBottomLeftRadius = '4px';
			myBox.style.MozBorderRadiusBottomleft = '4px';
			bdy.appendChild(myBox);
		}
	},

	/**
	 * @name Hyphenator-hyphenateWord
	 * @methodOf Hyphenator
	 * @description
	 * This function is the heart of Hyphenator.js. It returns a hyphenated word.
	 *
	 * If there's already a {@link Hyphenator-hypen} in the word, the word is returned as it is.
	 * If the word is in the exceptions list or in the cache, it is retrieved from it.
	 * If there's a '-' put a zeroWidthSpace after the '-' and hyphenate the parts.
	 * @param string The language of the word
	 * @param string The word
	 * @returns string The hyphenated word
	 * @public
	 */
	hyphenateWord = function (lang, word) {
		var lo = Hyphenator.languages[lang],
			parts, i, l, w, wl, s, hypos, p, maxwins, win, pat = false, patk, patl, c, digits, z, numb3rs, n, inserted, hyphenatedword;
		if (word === '') {
			return '';
		}
		if (word.indexOf(hyphen) !== -1) {
			//word already contains shy; -> leave at it is!
			return word;
		}
		if (enableCache && lo.cache.hasOwnProperty(word)) { //the word is in the cache
			return lo.cache[word];
		}
		if (lo.exceptions.hasOwnProperty(word)) { //the word is in the exceptions list
			return lo.exceptions[word].replace(/-/g, hyphen);
		}
		if (word.indexOf('-') !== -1) {
			//word contains '-' -> hyphenate the parts separated with '-'
			parts = word.split('-');
			for (i = 0, l = parts.length; i < l; i++) {
				parts[i] = hyphenateWord(lang, parts[i]);
			}
			return parts.join('-');
		}
		//finally the core hyphenation algorithm
		w = '_' + word + '_';
		wl = w.length;
		s = w.split('');
		w = w.toLowerCase();
		hypos = [];
		numb3rs = {'0': true, '1': true, '2': true, '3': true, '4': true, '5': true, '6': true, '7': true, '8': true, '9': true}; //check for member is faster then isFinite()
		n = wl - lo.shortestPattern;
		for (p = 0; p <= n; p++) {
			maxwins = Math.min((wl - p), lo.longestPattern);
			for (win = lo.shortestPattern; win <= maxwins; win++) {
				if (lo.patterns.hasOwnProperty(patk = w.substr(p, win))) {
					pat = lo.patterns[patk];
				} else {
					continue;
				}
				digits = 1;
				patl = pat.length;
				for (i = 0; i < patl; i++) {
					c = pat.charAt(i);
					if (numb3rs[c]) {
						if (i === 0) {
							z = p - 1;
							if (!hypos[z] || hypos[z] < c) {
								hypos[z] = c;
							}
						} else {
							z = p + i - digits;
							if (!hypos[z] || hypos[z] < c) {
								hypos[z] = c;
							}
						}
						digits++;
					}
				}
			}
		}
		inserted = 0;
		for (i = lo.leftmin; i <= (word.length - lo.rightmin); i++) {
			if (!!(hypos[i] & 1)) {
				s.splice(i + inserted + 1, 0, hyphen);
				inserted++;
			}
		}
		hyphenatedword = s.slice(1, -1).join('');
		if (enableCache) {
			lo.cache[word] = hyphenatedword;
		}
		return hyphenatedword;
	},

	/**
	 * @name Hyphenator-hyphenateURL
	 * @methodOf Hyphenator
	 * @description
	 * Puts {@link Hyphenator-urlhyphen} after each no-alphanumeric char that my be in a URL.
	 * @param string URL to hyphenate
	 * @returns string the hyphenated URL
	 * @public
	 */
	hyphenateURL = function (url) {
		return url.replace(/([:\/\.\?#&_,;!@]+)/gi, '$&' + urlhyphen);
	},

	/**
	 * @name Hyphenator-hyphenateElement
	 * @methodOf Hyphenator
	 * @description
	 * Takes the content of the given element and - if there's text - replaces the words
	 * by hyphenated words. If there's another element, the function is called recursively.
	 * When all words are hyphenated, the visibility of the element is set to 'visible'.
	 * @param object The element to hyphenate
	 * @param string The language used in this element
	 * @public
	 */
	hyphenateElement = function (el) {
		var hyphenatorSettings = Expando.getDataForElem(el),
			lang = hyphenatorSettings.language, hyphenate, n, i;
		if (Hyphenator.languages.hasOwnProperty(lang)) {
			hyphenate = function (word) {
				if (urlOrMailRE.test(word)) {
					return hyphenateURL(word);
				} else {
					return hyphenateWord(lang, word);
				}
			};
			i = 0;
			while (!!(n = el.childNodes[i++])) {
				if (n.nodeType === 3 && n.data.length >= min) { //type 3 = #text -> hyphenate!
					n.data = n.data.replace(Hyphenator.languages[lang].genRegExp, hyphenate);
				}
			}
		}
		if (hyphenatorSettings.isHidden && intermediateState === 'hidden') {
			el.style.visibility = 'visible';
			if (!hyphenatorSettings.hasOwnStyle) {
				el.setAttribute('style', ''); // without this, removeAttribute doesn't work in Safari (thanks to molily)
				el.removeAttribute('style');
			} else {
				if (el.style.removeProperty) {
					el.style.removeProperty('visibility');
				} else if (el.style.removeAttribute) { // IE
					el.style.removeAttribute('visibility');
				}
			}
		}
		if (hyphenatorSettings.isLast) {
			state = 3;
			onHyphenationDone();
		}
	},

	/**
	 * @name Hyphenator-removeHyphenationFromElement
	 * @methodOf Hyphenator
	 * @description
	 * Removes all hyphens from the element. If there are other elements, the function is
	 * called recursively.
	 * Removing hyphens is usefull if you like to copy text. Some browsers are buggy when the copy hyphenated texts.
	 * @param object The element where to remove hyphenation.
	 * @public
	 */
	removeHyphenationFromElement = function (el) {
		var h, i = 0, n;
		switch (hyphen) {
		case '|':
			h = '\\|';
			break;
		case '+':
			h = '\\+';
			break;
		case '*':
			h = '\\*';
			break;
		default:
			h = hyphen;
		}
		while (!!(n = el.childNodes[i++])) {
			if (n.nodeType === 3) {
				n.data = n.data.replace(new RegExp(h, 'g'), '');
				n.data = n.data.replace(new RegExp(zeroWidthSpace, 'g'), '');
			} else if (n.nodeType === 1) {
				removeHyphenationFromElement(n);
			}
		}
	},

	/**
	 * @name Hyphenator-hyphenateDocument
	 * @methodOf Hyphenator
	 * @description
	 * Calls hyphenateElement() for all members of elements. This is done with a setTimout
	 * to prevent a "long running Script"-alert when hyphenating large pages.
	 * Therefore a tricky bind()-function was necessary.
	 * @public
	 */
	hyphenateDocument = function () {
		function bind(fun, arg) {
			return function () {
				return fun(arg);
			};
		}
		var i = 0, el;
		while (!!(el = elements[i++])) {
			window.setTimeout(bind(hyphenateElement, el), 0);

		}
	},

	/**
	 * @name Hyphenator-removeHyphenationFromDocument
	 * @methodOf Hyphenator
	 * @description
	 * Does what it says ;-)
	 * @public
	 */
	removeHyphenationFromDocument = function () {
		var i = 0, el;
		while (!!(el = elements[i++])) {
			removeHyphenationFromElement(el);
		}
		state = 4;
	};

	return {

		/**
		 * @name Hyphenator.version
		 * @memberOf Hyphenator
		 * @description
		 * String containing the actual version of Hyphenator.js
		 * [major release].[minor releas].[bugfix release]
		 * major release: new API, new Features, big changes
		 * minor release: new languages, improvements
		 * @public
         */
		version: '2.4.0',

		/**
		 * @name Hyphenator.languages
		 * @memberOf Hyphenator
		 * @description
		 * Objects that holds key-value pairs, where key is the language and the value is the
		 * language-object loaded from (and set by) the pattern file.
		 * The language object holds the following members:
		 * <table>
		 * <tr><th>key</th><th>desc></th></tr>
		 * <tr><td>leftmin</td><td>The minimum of chars to remain on the old line</td></tr>
		 * <tr><td>rightmin</td><td>The minimum of chars to go on the new line</td></tr>
		 * <tr><td>shortestPattern</td><td>The shortes pattern (numbers don't count!)</td></tr>
		 * <tr><td>longestPattern</td><td>The longest pattern (numbers don't count!)</td></tr>
		 * <tr><td>specialChars</td><td>Non-ASCII chars in the alphabet.</td></tr>
		 * <tr><td>patterns</td><td>the patterns</td></tr>
		 * </table>
		 * And optionally (or after prepareLanguagesObj() has been called):
		 * <table>
		 * <tr><td>exceptions</td><td>Excpetions for the secified language</td></tr>
		 * </table>
		 * @public
         */
		languages: {},


		/**
		 * @name Hyphenator.config
		 * @methodOf Hyphenator
		 * @description
		 * Config function that takes an object as an argument. The object contains key-value-pairs
		 * containig Hyphenator-settings. This is a shortcut for calling Hyphenator.set...-Methods.
		 * @param object <table>
		 * <tr><th>key</th><th>values</th><th>default</th></tr>
		 * <tr><td>classname</td><td>string</td><td>'hyphenate'</td></tr>
		 * <tr><td>minwordlength</td><td>integer</td><td>6</td></tr>
		 * <tr><td>hyphenchar</td><td>string</td><td>'&amp;shy;'</td></tr>
		 * <tr><td>urlhyphenchar</td><td>string</td><td>'zero with space'</td></tr>
		 * <tr><td>togglebox</td><td>function</td><td>see code</td></tr>
		 * <tr><td>displaytogglebox</td><td>boolean</td><td>false</td></tr>
		 * <tr><td>remoteloading</td><td>boolean</td><td>true</td></tr>
		 * <tr><td>onhyphenationdonecallback</td><td>function</td><td>empty function</td></tr>
		 * <tr><td>onerrorhandler</td><td>function</td><td>alert(onError)</td></tr>
		 * <tr><td>intermediatestate</td><td>string</td><td>'hidden'</td></tr>
		 * </table>
		 * @public
		 * @example &lt;script src = "Hyphenator.js" type = "text/javascript"&gt;&lt;/script&gt;
         * &lt;script type = "text/javascript"&gt;
         *     Hyphenator.config({'minwordlength':4,'hyphenchar':'|'});
         *     Hyphenator.run();
         * &lt;/script&gt;
         */
		config: function (obj) {
			var assert = function (name, type) {
					if (typeof obj[name] === type) {
						return true;
					} else {
						onError(new Error('Config onError: ' + name + ' must be of type ' + type));
						return false;
					}
				},
				key;
			for (key in obj) {
				if (obj.hasOwnProperty(key)) {
					switch (key) {
					case 'classname':
						if (assert('classname', 'string')) {
							hyphenateClass = obj.classname;
						}
						break;
					case 'donthyphenateclassname':
						if (assert('donthyphenateclassname', 'string')) {
							dontHyphenateClass = obj.donthyphenateclassname;
						}
						break;
					case 'minwordlength':
						if (assert('minwordlength', 'number')) {
							min = obj.minwordlength;
						}
						break;
					case 'hyphenchar':
						if (assert('hyphenchar', 'string')) {
							if (obj.hyphenchar === '&shy;') {
								obj.hyphenchar = String.fromCharCode(173);
							}
							hyphen = obj.hyphenchar;
						}
						break;
					case 'urlhyphenchar':
						if (obj.hasOwnProperty('urlhyphenchar')) {
							if (assert('urlhyphenchar', 'string')) {
								urlhyphen = obj.urlhyphenchar;
							}
						}
						break;
					case 'togglebox':
						if (assert('togglebox', 'function')) {
							toggleBox = obj.togglebox;
						}
						break;
					case 'displaytogglebox':
						if (assert('displaytogglebox', 'boolean')) {
							displayToggleBox = obj.displaytogglebox;
						}
						break;
					case 'remoteloading':
						if (assert('remoteloading', 'boolean')) {
							enableRemoteLoading = obj.remoteloading;
						}
						break;
					case 'enablecache':
						if (assert('enablecache', 'boolean')) {
							enableCache = obj.enablecache;
						}
						break;
					case 'onhyphenationdonecallback':
						if (assert('onhyphenationdonecallback', 'function')) {
							onHyphenationDone = obj.onhyphenationdonecallback;
						}
						break;
					case 'onerrorhandler':
						if (assert('onerrorhandler', 'function')) {
							onError = obj.onerrorhandler;
						}
						break;
					case 'intermediatestate':
						if (assert('intermediatestate', 'string')) {
							intermediateState = obj.intermediatestate;
						}
						break;
					case 'selectorfunction':
						if (assert('selectorfunction', 'function')) {
							selectorFunction = obj.selectorfunction;
						}
						break;
					default:
						onError(new Error('Hyphenator.config: property ' + key + ' not known.'));
					}
				}
			}
		},

		/**
		 * @name Hyphenator.run
		 * @methodOf Hyphenator
		 * @description
		 * Bootstrap function that starts all hyphenation processes when called.
		 * @public
		 * @example &lt;script src = "Hyphenator.js" type = "text/javascript"&gt;&lt;/script&gt;
         * &lt;script type = "text/javascript"&gt;
         *   Hyphenator.run();
         * &lt;/script&gt;
         */
		run: function () {
			var process = function () {
				try {
					autoSetMainLanguage();
					gatherDocumentInfos();
					prepare(hyphenateDocument);
					if (displayToggleBox) {
						toggleBox(true);
					}
				} catch (e) {
					onError(e);
				}
			};
			if (!documentLoaded) {
				runOnContentLoaded(window, process);
			}
			if (Hyphenator.isBookmarklet() || documentLoaded) {
				process();
			}
		},

		/**
		 * @name Hyphenator.addExceptions
		 * @methodOf Hyphenator
		 * @description
		 * Adds the exceptions from the string to the appropriate language in the
		 * {@link Hyphenator-languages}-object
		 * @param string The language
		 * @param string A comma separated string of hyphenated words WITH spaces.
		 * @public
		 * @example &lt;script src = "Hyphenator.js" type = "text/javascript"&gt;&lt;/script&gt;
         * &lt;script type = "text/javascript"&gt;
         *   Hyphenator.addExceptions('de','ziem-lich, Wach-stube');
         *   Hyphenator.run();
         * &lt;/script&gt;
         */
		addExceptions: function (lang, words) {
			if (lang === '') {
				lang = 'global';
			}
			if (exceptions.hasOwnProperty[lang]) {
				exceptions[lang] += ", " + words;
			} else {
				exceptions[lang] = words;
			}
		},

		/**
		 * @name Hyphenator.hyphenate
		 * @methodOf Hyphenator
		 * @public
		 * @description
		 * Hyphenates the target. The language patterns must be loaded.
		 * If the target is a string, the hyphenated string is returned,
		 * if it's an object, the values are hyphenated directly.
		 * @param mixed the target to be hyphenated
		 * @param string the language of the target
		 * @returns string
		 * @example &lt;script src = "Hyphenator.js" type = "text/javascript"&gt;&lt;/script&gt;
		 * &lt;script src = "patterns/en.js" type = "text/javascript"&gt;&lt;/script&gt;
         * &lt;script type = "text/javascript"&gt;
		 * var t = Hyphenator.hyphenate('Hyphenation', 'en'); //Hy|phen|ation
		 * &lt;/script&gt;
		 */
		hyphenate: function (target, lang) {
			var hyphenate, n, i;
			if (Hyphenator.languages.hasOwnProperty(lang)) {
				if (!Hyphenator.languages[lang].prepared) {
					prepareLanguagesObj(lang);
				}
				hyphenate = function (word) {
					if (urlOrMailRE.test(word)) {
						return hyphenateURL(word);
					} else {
						return hyphenateWord(lang, word);
					}
				};
				if (typeof target === 'string' || target.constructor === String) {
					return target.replace(Hyphenator.languages[lang].genRegExp, hyphenate);
				} else if (typeof target === 'object') {
					i = 0;
					while (!!(n = target.childNodes[i++])) {
						if (n.nodeType === 3 && n.data.length >= min) { //type 3 = #text -> hyphenate!
							n.data = n.data.replace(Hyphenator.languages[lang].genRegExp, hyphenate);
						} else if (n.nodeType === 1) {
							if (n.lang !== '') {
								lang = n.lang;
							}
							Hyphenator.hyphenate(n, lang);
						}
					}
				}
			} else {
				onError(new Error('Language "' + lang + '" is not loaded.'));
			}
		},

		/**
		 * @name Hyphenator.isBookmarklet
		 * @methodOf Hyphenator
		 * @description
		 * Returns {@link Hyphenator-isBookmarklet}.
		 * @returns boolean
		 * @public
         */
		isBookmarklet: function () {
			return isBookmarklet;
		},


		/**
		 * @name Hyphenator.toggleHyphenation
		 * @methodOf Hyphenator
		 * @description
		 * Checks the current state of the ToggleBox and removes or does hyphenation.
		 * @public
         */
		toggleHyphenation: function () {
			switch (state) {
			case 3:
				removeHyphenationFromDocument();
				toggleBox(false);
				break;
			case 4:
				hyphenateDocument();
				toggleBox(true);
				break;
			}
		}
	};
}());
if (Hyphenator.isBookmarklet()) {
	Hyphenator.config({displaytogglebox: true, intermediatestate: 'visible'});
	Hyphenator.run();
}


