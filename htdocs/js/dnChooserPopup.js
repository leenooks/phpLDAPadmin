function dnChooserPopup(form_element,rdn)
{
	mywindow=open('entry_chooser.php','myname','resizable=no,width=600,height=370,scrollbars=1');
	mywindow.location.href = 'entry_chooser.php?form_element=' + form_element + '&rdn=' + rdn;
	if (mywindow.opener == null) mywindow.opener = self;
}
