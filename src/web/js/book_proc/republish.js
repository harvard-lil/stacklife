
function loadTheBook () {
  var epuburl = document.getElementById('epuburl').value;
  loadBook(epuburl);
}

function setPageSize(pageRatio) {

  if (!pageRatio) pageRatio = Math.sqrt(Math.E);

  var width = (window.innerWidth - 20) / 2;
  var height = window.innerHeight - 20;

  var pageHeight = height,
      pageWidth = width;

  if (height / pageRatio > width) {
    // We'll need to constrain the height.
    pageHeight = width * pageRatio;
/*        } else if (height / pageRatio > width * 1.5) {
    // We have way more height than width. Go to a one-page view.
    pageHeight = width * pageRatio; */
  } else {
    // Constrain the width, 2-page.
    pageWidth = height / pageRatio;
  }

  console.log("Width/Height: " + width + "/" + height + ", pageWidth/pageHeight: " + pageWidth + "/" + pageHeight);

  var container = document.getElementById('container');
  container.style.width = pageWidth * 2;
  container.style.height = pageHeight;

  var background = document.getElementById('backgroundDiv');
  background.style.width = pageWidth * 2;
  background.style.height = pageHeight;

  var canvas = document.getElementById('background');
  canvas.width = pageWidth * 2;
  canvas.height = pageHeight;

  var pages = document.getElementsByClassName('page');

  for (var i = 0, l = pages.length; i < l; i++) {
    pages[i].style.width = pageWidth - Math.log(22) * 8;
    pages[i].style.height = pageHeight - Math.log(22) * 10;
    pages[i].style.marginTop = '0px';

    var header = document.getElementsByClassName('header')[0];
    var headerHeight = 24 * 2; // hard coded for now, until we properly size the header...

    var contents = document.getElementsByClassName('content');
    contents[0].style.height = pageHeight - (Math.log(22) * 10) - headerHeight;
  }

// This needs tweaking, because it should actually be done once we know
// which font will be used as the body font for the book. This is a reasonable
  // approximation, though, and will do for now.

  var sizerElement = document.createElement('p');

  // Rough statistical relevancy of letters [in english]. This will give us a more-accurate approximation of average line length.
  var letters = "aaaaaaaabbcccddddeeeeeeeeeeeeeffgghhhhhhiiiiiiijkllllmmnnnnnnnooooooooppqrrrrrrsssssstttttttttuuuvwxyyz";
  sizerElement.textContent = letters;
  sizerElement.style.left = '0';
  sizerElement.style.top = '0';
  sizerElement.style.position = 'absolute';
  sizerElement.style.margin = '0';
  sizerElement.style.padding = '0';
  document.body.appendChild(sizerElement);

  // Our ideal line width is 66 characters.
console.log('offsetwidth: ' +  sizerElement.offsetWidth);
console.log('letters.length: ' + letters.length);
  var idealLineWidth = (sizerElement.offsetWidth / letters.length) * 66;
  var measuredFontSize = document.defaultView.getComputedStyle(sizerElement, null).getPropertyValue('font-size').toString().replace('px', '');
console.log('measured font size: ' + measuredFontSize);

  // now that we have that, remove the sizer element.
  document.body.removeChild(sizerElement);

  var pageSize = pageWidth - Math.log(22) * 8
console.log('content width: ' + pageSize);

  // multiply the actual fontSize by our sizing ratio
  var pixelFontSize = (measuredFontSize * pageSize / idealLineWidth) + 'px';
console.log('pixelFontSize: ' + pixelFontSize);

  // and apply that to the document body. All other sizes should be in ems.
  document.body.style.fontSize = pixelFontSize;
}

var loadBook = function (epub) {

  setPageSize();


  selector.style.display = 'none';
  container.style.display = 'block';

  var epubUrl;
  if (epub.substr(0, 4) == 'http') {
    epubUrl = epub;
  } else {
    epubUrl = "epubs/" + epub;
  }

  ePub.open(epubUrl, function (book) {

    var te = document.getElementById('book_title');
    te.textContent = book.title;

    var ae = document.getElementById('author_heading');
    ae.textContent = book.author;

    var lc = document.getElementById('leftcontent'),
        rc = document.getElementById('rightcontent'),
        ln = document.getElementsByClassName('left pagenum')[0],
        rn = document.getElementsByClassName('right pagenum')[0];

    var pages;
    if (window.orientation == 0 || window.orientation == 180) {
      pages = [lc];
    } else {
      pages = [lc, rc];
    }

    pageHandler = new PageHandler(book, pages, [ln, rn]);

    var contents = document.getElementById('contents');
    for (var i = 0, l = book.toc.length; i < l; i++) {

      // Sometimes navpoints aren't all covered, or they are done so in weird ways.
      // try to be liberal about things.
      if (book.toc[i] === undefined) continue;

      var chapter = document.createElement('a');
      var secName = book.toc[i].fileName;
      chapter.setAttribute('href', '#section=' + secName);
      chapter.textContent = book.toc[i].title;
      chapter.onclick = function (secName) {
        return function () {
          pageHandler.goToSection(secName);
          pageHandler.display();
          contents.style.display = 'none';
        }
      }(secName);
      contents.appendChild(chapter);
    }

    pageHandler.display();

    swipe(null, pageHandler);

    function handleArrowKeys(evt) {
        evt = (evt) ? evt : ((window.event) ? event : null);
        if (evt) {
            switch (evt.keyCode) {
                case 37:
                    pageHandler.prevPage();
                    break;    
                case 39:
                    pageHandler.nextPage();
                    break;    
                case 67:
                    document.getElementById('contents').style.display = 'block';
                    break;
             }
        }
    }

    document.onkeyup = handleArrowKeys;

    // Set up an orientation handler
    window.onorientationchange = function () {
      if (window.orientation == 0 || window.orientation == 180) {
        pageHandler.setPages([lc]);
      } else {
        pageHandler.setPages([lc,rc]);
      }
      pageHandler.display();
    }

  });
}