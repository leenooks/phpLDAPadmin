//
// Purpose of this file is to remap characters as ASCII characters
//
// 

var to_ascii_array = new Array();
to_ascii_array['Ã '] = 'a';
to_ascii_array['Ã¡'] = 'a';
to_ascii_array['Ã¢'] = 'a';
to_ascii_array['Ã€'] = 'a';
to_ascii_array['Ã£'] = 'a';
to_ascii_array['Ã¥'] = 'a';
to_ascii_array['Ã'] = 'A';
to_ascii_array['Ã'] = 'A';
to_ascii_array['Ã'] = 'A';
to_ascii_array['Ã'] = 'A';
to_ascii_array['Ã'] = 'A';
to_ascii_array['Ã'] = 'A';
to_ascii_array['Ã©'] = 'e';
to_ascii_array['Ãš'] = 'e';
to_ascii_array['Ã«'] = 'e';
to_ascii_array['Ãª'] = 'e';
to_ascii_array['â¬'] = 'E';
to_ascii_array['Ã¯'] = 'i';
to_ascii_array['Ã®'] = 'i';
to_ascii_array['Ã¬'] = 'i';
to_ascii_array['Ã­'] = 'i';
to_ascii_array['Ã'] = 'I';
to_ascii_array['Ã'] = 'I';
to_ascii_array['Ã'] = 'I';
to_ascii_array['Ã'] = 'I';
to_ascii_array['Ã²'] = 'o';
to_ascii_array['Ã³'] = 'o';
to_ascii_array['ÃŽ'] = 'o';
to_ascii_array['Ãµ'] = 'o';
to_ascii_array['Ã¶'] = 'o';
to_ascii_array['Ãž'] = 'o';
to_ascii_array['Ã'] = 'O';
to_ascii_array['Ã'] = 'O';
to_ascii_array['Ã'] = 'O';
to_ascii_array['Ã'] = 'O';
to_ascii_array['Ã'] = 'O';
to_ascii_array['Ã'] = 'O';
to_ascii_array['Ã¹'] = 'u';
to_ascii_array['Ãº'] = 'u';
to_ascii_array['ÃŒ'] = 'u';
to_ascii_array['Ã»'] = 'u';
to_ascii_array['Ã'] = 'U';
to_ascii_array['Ã'] = 'U';
to_ascii_array['Ã'] = 'U';
to_ascii_array['Ã'] = 'U';
to_ascii_array['ÃŠ'] = 'ae';
to_ascii_array['Ã'] = 'AE';
to_ascii_array['Ãœ'] = 'y';
to_ascii_array['Ã¿'] = 'y';
to_ascii_array['Ã'] = 'SS';
to_ascii_array['Ã'] = 'C';
to_ascii_array['Ã§'] = 'c';
to_ascii_array['Ã'] = 'N';
to_ascii_array['Ã±'] = 'n';
to_ascii_array['Â¢'] = 'c';
to_ascii_array['Â©'] = '(C)';
to_ascii_array['Â®'] = '(R)';
to_ascii_array['Â«'] = '<<';
to_ascii_array['Â»'] = '>>';

function toAscii(text) {
    //var text = field.value;
    var position = 0;
    var output = "";
    for (position = 0 ; position < text.length ; position++) {
        var tmp = text.substring(position,position+1);
        if (to_ascii_array[tmp] != undefined) {
            tmp = to_ascii_array[tmp];
        }
        output = output + tmp;
    }
    return output;
}

