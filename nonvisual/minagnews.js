// used with regular editor (not TinyMCE)
function getContentSelection(win){
	var word = '', sel, startPos, endPos;
	if (document.selection) {
		win.edCanvas.focus();
	    sel = document.selection.createRange();
		if (sel.text.length > 0) {
			word = sel.text;
		}
	}
	else if (win.edCanvas.selectionStart || win.edCanvas.selectionStart == '0') {
		startPos = win.edCanvas.selectionStart;
		endPos = win.edCanvas.selectionEnd;
		if (startPos != endPos) {
			word = win.edCanvas.value.substring(startPos, endPos);
		}
	}
	return word;
}

function insertDocLink(elem){
	elem = jQuery(elem);
	var winder = window.top;	
	var href,title = '',text;
	var word = getContentSelection(winder);
	if(word.length == 0){
		var text = elem.text();
	}
	else{
		var text = word;
	}
	var href = elem.attr('href');
	var title = elem.text();

	var link = '<a href="'+href+'" title="'+title+'" '+'>'+text+'</a>';	

  winder.edInsertContent(winder.edCanvas, link);
	winder.tb_remove();
	return false;
}

function insertTableShortcode(){

	  
	  var winder = window.top;	
    showOption = '';
    
    for( i = 0; i < document.insertTableForm.docType.length; i++ ) {
      if( document.insertTableForm.docType[i].checked == true ) {
        showOption = document.insertTableForm.docType[i].value;
        break;
      }
    }
    
    if (showOption == '') {
        showOption = 'All';
    }
    
    shortcode = '[minagnews-table show='+showOption.toLowerCase()+'] '
    
    
    winder.edInsertContent(winder.edCanvas, shortcode);
	  winder.tb_remove();
    
    
    return false;
}