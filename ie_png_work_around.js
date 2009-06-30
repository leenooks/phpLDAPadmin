function fixIEPNG( img )
{
   img.style.filter =
      "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"
      + img.src + "', enabled=true)";
   img.src="blank.gif";
}

function checkPNGs()
{
   // test to see if the browser is IE
   var agent = navigator.userAgent.toLowerCase();
   var is_ie = (( agent.indexOf("msie")  != -1 ) &&
                ( agent.indexOf("opera") == -1 ));

   // if IE, use DirectX to correctly display a PNG
   if ( !is_ie ) return;

   // go through each image in the page and fix them
   for ( var i = 0; i < document.images.length; i++ )
   {
      // only if the image is a png
      var img = document.images[ i ];
      if ( img.src.indexOf( "png" ) != -1 )
         fixIEPNG( img );
   }
}

checkPNGs();
