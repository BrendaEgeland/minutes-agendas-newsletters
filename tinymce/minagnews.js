// Used for visual editor 
function init() {
	tinyMCEPopup.resizeToInnerSize();
}
function insertDocLink(elem){

	elem = jQuery(elem);

	var ed = tinyMCEPopup.editor;
	var dom = ed.dom;
  // the following line solves the IE issue where this script would not work.
	tinyMCEPopup.restoreSelection();
	var se = ed.selection;
	
  if (se.isCollapsed()) {
      // Create a link when there is no selection
      newlink = '<a href="'+elem.attr('href')+'" title="'+elem.text()+'" '+'>'+elem.text()+'</a>'; 

      se.setContent(newlink);
  } else {
      n = ed.selection.getNode();
      e = dom.getParent(n, 'A'); 
       if(e == null){

         tinyMCEPopup.execCommand("CreateLink", false, "#mce_temp_url#", {skip_undo : 1});
         tinymce.each(ed.dom.select("a"), function(n) {
           if (ed.dom.getAttrib(n, 'href') == '#mce_temp_url#') {
             e = n;
             ed.dom.setAttribs(e, {
               title : elem.text()
             });
             ed.dom.setAttribs(e, {
               href : elem.attr('href')
             });
           }
         });
  
       }
       else{
           ed.dom.setAttribs(e, {
             title : elem.text()
           });
           ed.dom.setAttribs(e, {
             href : elem.attr('href')
           }); 
  
       }
  }


	tinyMCEPopup.close();

	return false;
}

function insertTableShortcode(){

	  var ed = tinyMCEPopup.editor, dom = ed.dom, se = ed.selection;
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
    se.setContent(shortcode);
    
    tinyMCEPopup.close();
    
    return false;
}

