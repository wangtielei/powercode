
// Get the handle of applications main window
var window = UIATarget.localTarget().frontMostApp().mainWindow(); 

// Get the handle of view
var view = window.elements()[0];

var textfields = window.textFields();
var passwordfields = window.secureTextFields();
var buttons = window.buttons();
var textviews = window.textViews();
var statictexts = window.staticTexts();

var target = UIATarget.localTarget();

// Check number of Text field(s)
if(textfields.length!=1)
{
    UIALogger.logFail("FAIL: Inavlid number of Text field(s)");
}
else
{
    UIALogger.logPass("PASS: Correct number of Text field(s)");
}

// Check number of Secure field(s)

if(passwordfields.length!=1)
{
    UIALogger.logFail("FAIL: Inavlid number of Secure field(s)");
} 
else 
{
    UIALogger.logPass("PASS: Correct number of Secure field(s)");
}

// Check number of static field(s)
if(statictexts.length!=2)
{
    UIALogger.logFail("FAIL: Inavlid number of static field(s)");
} 
else 
{
    UIALogger.logPass("PASS: Correct number of static field(s)");
}
// Check number of buttons(s)
if(buttons.length!=1)
{
    UIALogger.logFail("FAIL: Inavlid number of button(s)");
} 
else 
{
    UIALogger.logPass("PASS: Correct number of button(s)");
}

//TESTCASE_001 : Test Log on Screen

//Check existence of desired TextField On UIScreen
if(textfields["username"]==null  || textfields["username"].toString() == "[object UIAElementNil]")
{
    UIALogger.logFail("FAIL:Desired textfield not found.");
}
else
{
    UIALogger.logPass("PASS: Desired UITextField is available");
}

//TESTCASE_1.2 :Check existence desired of PasswordField On UIScreen
if(passwordfields[0]==null || passwordfields[0].toString() == "[object UIAElementNil]")
{
    UIALogger.logFail("FAIL:Desired UISecureField not found.");
}
else
{
    UIALogger.logPass("PASS: Desired UISecureField is available");
}

//TESTCASE_1.3 :Check For Existence of Buttons On UIScreen
if(buttons["logon"]==null || buttons["logon"].toString() == "[object UIAElementNil]")
{
    UIALogger.logFail("FAIL:Desired UIButton not found.");
}
else
{
    UIALogger.logPass("PASS: Desired UIButton is available");
}

//TESTCASE_001 : Missing User Name
///////////////////////////////////////
textfields["username"].setValue("");
passwordfields[0].setValue("password");
buttons["logon"].tap();
//target.delay(2);
var errorVal=textviews["error"].value();
if(errorVal!="Invalid User Name or Password")
{
    UIALogger.logFail("Did Not Get Missing UserName Error : "+errorVal);
}
else
{
    UIALogger.logPass("Missing User Name");
}

//TESTCASE_002 : Missing Password
////////////////////////////////////////////////
textfields["username"].setValue("username");
passwordfields[0].setValue("");
buttons["logon"].tap();
target.delay(2);
var errorVal=textviews["error"].value();
if(errorVal!="Invalid User Name or Password")
{
    UIALogger.logFail("Did Not Get Missing Password Error : "+errorVal);
}
else
{
    UIALogger.logPass(" Missing Password");
}

//TESTCASE_003 : Successful Log On 
textfields["username"].setValue("username");
passwordfields[0].setValue("password");
buttons["logon"].tap();
target.delay(2);
