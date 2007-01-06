	**FormMaker**

With this class it is realy easy to create forms in a w3c conform layout. 
It can handle any number of forms with any number of inputs you want and
supports [select] [text] [password] inputs and by the next version it shall
even support [textarea] inputs.
If you got the inputvalues done by a user just give it to the class and it can
check the values for you. Possible checks are [empty], [is_email], [is_icq], 
[is_same_value_as], [starts_with], [ends_with], ...
The class adds errormessages to the local array so that the outputgenerator can
display them easily.

The class adds each formular as an array to a local variable and adds the inputs 
to a subarray. Using this structure the class supports easy methods of output or 
you can use the build in OutputGenerator. 
If your homepage system works with the XHTML-code-generator ComaLate you can  even 
use a ComaLate template for the FormMaker output and the class even sets the 
replacements automaticaly for you. Just call to 
function GenerateTemplate(&$ComaLate, $GiveErrorInformation); For setting 
the replacements the function needs a link to the ComaLate class and the information,
wether it should give out errorinformations or not. This is useful if you display the
form the first time so that the user has to give the informations at first. By 
displaying the form the second time you can give out errorinformations easily.

Just look to the PHP Documentation in the ./Documentation/ folder if need detailed 
information about the formmaker class.