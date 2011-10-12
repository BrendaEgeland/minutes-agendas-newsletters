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
    var opts = new Array();

    showOption = '';
    
    for( i = 0; i < document.insertTableForm.docType.length; i++ ) {
      if( document.insertTableForm.docType[i].checked == true ) {
        thisDocType = document.insertTableForm.docType[i].value;
        if (thisDocType == 'all') {
          showOption = 'all';
          break;
        }
        opts.push( document.insertTableForm.docType[i].value);
      }
    }

    if (showOption != 'all') {
      showOption = opts.toString();
      if (showOption == '') {
        showOption = 'all';
      }
    }
    
    var yearOpts = new Array();
    yearOption = '';
    if (true || document.insertTableForm.year != 'undefined') {
      for( i = 0; i < document.insertTableForm.year.length; i++ ) {
        if( document.insertTableForm.year[i].checked == true ) {
          thisYear = document.insertTableForm.year[i].value;
          if (thisYear == 'all') {
            yearOption = 'all';
            break;
          }
          yearOpts.push( document.insertTableForm.year[i].value);
        }
      }
      if (yearOption != 'all') {
        yearOption = yearOpts.toString();
        if (yearOption == '') {
          yearOption = 'all';
        }
      }
    } else {
      yearOption = 'all';
    }
    
    shortcode = '[minagnews-table show='+showOption.toLowerCase()+' year='+yearOption.toLowerCase()+'] ';
    
    
    winder.edInsertContent(winder.edCanvas, shortcode);
	  winder.tb_remove();
    
    
    return false;
}