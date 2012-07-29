Coding Rules
============

1. Coding conventions
---------------------

To keep the code clear, I've choose the Zend Coding Conventions :
http://framework.zend.com/manual/en/coding-standard.coding-style.html

For NetBeans IDE, you can enable automatic formating with the following 
configuration :

Go to : Tools / Options / Editor icon / Formatting Tab

Languages Combo : PHP 

* Category : Tabs And indents

Check Override global options
Check expand tabs to spaces
Number of spaces per indent : 4
Tab size                    : 4
Margin size                 : 80
Initial indentation         : 0
Continuation indentation    : 4
Array declaration indent    : 4

* Category : Braces

Class Declaration           : New line
Method Declaration          : New line

To use the automatic formating : ALT+SHIFT+F

2. Design rules
---------------

- Be pragmatic, follow the K.I.S.S and D.R.Y philosophy
- Follow the S.O.L.I.D rules
- Avoid using statics or globals, they are evil

The *MOST* important rule is O.C.P : you never should change
the code to modify its behavior or add a new functionality.

