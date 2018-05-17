
// ==================================================================
// Test code
// ------------------------------------------------------------------

console.log('strating');
var t = new Tags;
t.open('p', {'class':'pclass'});
t.open('hr');
t.content('this is paragraph content')
t.open ('b');
t.warn('bold warning')
t.content('this is bold');
t.close();
t.content('more paragraph content');
t.closeAll();
t.open('p', {'class':'second'});
t.content('Second paragraph content');
t.open('ol');
t.open('li');
t.content('first list item');
t.close();
t.open('li', {'class':'warning'});
t.content('second list item');
t.warn('putting a warning right here');
t.close();
t.open('ul')
t.open('li')
t.content('first item on nested list');
t.close();
t.open('ul', {'class':'nestedUL'});
t.open('li')
t.content('deeply nested list item')
t.closeTo('ul');
t.open('li');
t.content('item for the first opened UL');
t.closeAll();
t.open('h2', {'class':'secondHalf'})
t.content("second half of document")
t.close();
t.open('p');
t.content('this paragraph is under the h2 in the second half of the document');
t.open('diagnostic', {'info': 'this is a diagnostic tag'})
t.clear();
t.getWarnings();
