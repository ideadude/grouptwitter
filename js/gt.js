//ask before deleting, etc
function askfirst(text, url)
{
	var answer = confirm (text);
	
	if (answer)
		window.location=url;
}

//provide a random timestamp with each call to foil caching
function getTimestamp()
{
	var t = new Date();
	var r = "" + t.getFullYear() + t.getMonth() + t.getDate() + t.getHours() + t.getMinutes() + t.getSeconds(); 

	return(r);
}

//service call to get twitter account number
function getTwitterAccountNumber(a)
{
	var url = '/wp-content/plugins/grouptwitter/services/gettwitteraccountnumber.php';
	var pars = 'a=' + a + '&ts=' + getTimestamp();
	
	var myAjax = new Ajax.Updater('accountnumber', url, { method: 'get', parameters: pars, evalScripts: true }); 
}
