$(document).ready(function() {


$('.library_link').live('click', function() {

$('#results').text('I\'m looking for results for you now. Hold tight, little buddy.');

var results_table = "<table>"
        console.log($('#results').val('#searchbox'));

$.getJSON('/librarycloud/v.3/api/item/', $.param({
        'search_type': 'keyword',
        'key': 'BUILD-LC-KEY',
        'start': 0,
        'limit': 10,
        'sort': 'shelfrank desc',
        'filter': 'source:' + this.id,
        'query': $('#searchbox').val()
    }), function(results) {
           if (results.num_found === 0) {
               $('#results').text('No results');
           } else {
              $.each(results.docs, function(i, item) {
                  results_table += '<tr>';
                  results_table += '<td>';
                  results_table += item.title;
                  results_table += '</td>';
                  results_table += '<td>';
                  results_table += item.creator;
                  results_table += '</td>';
                  results_table += '</tr>';
              });
              $('#results').html(results_table); 
           }
        }
);
});
});
