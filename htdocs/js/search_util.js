// File: search_util.js
// Purpose:
// This JavaScript file defines some functions used by the two search forms for
// auto-populating the base DN dynamically when a server is selected from the
// drop-down.
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/js/search_util.js,v 1.2 2004/03/19 20:17:51 i18phpldapadmin Exp $

//the array to store the server
var servers = new Array();


//---------------------------------------------------------------------
//		Definition of the object server			      	
//---------------------------------------------------------------------

//constructor of the server
//param id the id of the server
//param name the name of the server
//param base_dn the base dn of the server

function server(id,name,base_dn){

	//the properties of the object
	this.id =id;
	this.name = name;
	this.base_dn = base_dn;

	// the method of the server
	this.getId=getId;
	this.setId=setId;
	this.getName = getName;
	this.setName = setName;
	this.setBaseDn = setBaseDn;
	this.getBaseDn = getBaseDn;
}
// set the id of the server
function setId(id){
	this.id = id;
}	
	
//return the id of the server
function getId(){
	return this.id;
}

// set the name of the server
function setName(name){
	this.name = name;
}	

// return the name of the server	
function getName(){
	return this.name;
}

// return the base dn of the server
function getBaseDn(){
	return this.base_dn;
}	

// set the base dn of the server
function setBaseDn(base_dn){
	this.base_dn = base_dn;
}

//-----------------------------------------------------------------------
//			End of the definition of the server
//-----------------------------------------------------------------------


// add a server object to the array of server
function addToServersList(obj_server){
	servers[servers.length] = obj_server;
}


