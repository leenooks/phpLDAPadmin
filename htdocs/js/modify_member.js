var m1 = document.getElementById('member').notmembers;
var m2 = document.getElementById('member').members;

/* This function generates hidden input array from new group members
 * when submit button is pressed.
 * see modify_member_form.php
*/
function update_new_values(memberattr) {
	el = document.getElementById("dnu");

	for (i=0;i<m2.length;i++) {
		el.innerHTML =
			el.innerHTML +
			"<input type='hidden' name='new_values[" + memberattr + "][" + i + "]' value='" + m2.options[i].text + "' />";
	}
}


/* This function moves members from left select box to right one
 * see modify_member_form.php
 */
function one2two() {
	m1len = m1.length ;

	for (i=0;i<m1len;i++) {
		if (m1.options[i].selected == true) {
			m2len = m2.length;
			m2.options[m2len]= new Option(m1.options[i].text);
		}
	}

	for (i=(m1len-1);i>=0;i--){
		if (m1.options[i].selected == true) {
			m1.options[i] = null;
		}
	}
}

/* This function moves members from right select box to left one
 * see modify_member_form.php
 */
function two2one() {
	m2len = m2.length ;

	for (i=0;i<m2len;i++){
		if (m2.options[i].selected == true) {
			m1len = m1.length;
			m1.options[m1len]= new Option(m2.options[i].text);
		}
	}

	for (i=(m2len-1);i>=0;i--) {
		if (m2.options[i].selected == true) {
			m2.options[i] = null;
		}
	}
}

/* This function moves all members from left select box to right one
 * see modify_member_form.php
 */
function all2two() {
	m1len = m1.length ;

	for (i=0;i<m1len;i++) {
		m2len = m2.length;
		m2.options[m2len]= new Option(m1.options[i].text);
	}

	for (i=(m1len-1);i>=0;i--) {
		m1.options[i] = null;
	}
}

/* This function moves all members from right select box to left one
 * see modify_member_form.php
 */
function all2one() {
	m2len = m2.length ;

	for (i=0;i<m2len;i++) {
		m1len = m1.length;
		m1.options[m1len]= new Option(m2.options[i].text);
	}

	for (i=(m2len-1);i>=0;i--) {
		m2.options[i] = null;
	}
}
