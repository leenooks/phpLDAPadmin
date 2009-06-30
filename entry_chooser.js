// $Header: /cvsroot/phpldapadmin/phpldapadmin/entry_chooser.js,v 1.2 2004/03/19 20:18:41 i18phpldapadmin Exp $
function dnChooserPopup( form_element )
{
	mywindow=open('entry_chooser.php','myname','resizable=no,width=600,height=370,scrollbars=1');
	mywindow.location.href = 'entry_chooser.php?form_element=' + form_element;
	if (mywindow.opener == null) mywindow.opener = self;
}
