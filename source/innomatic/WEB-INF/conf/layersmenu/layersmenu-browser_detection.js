// PHP Layers Menu 2.2.1 (C) 2001-2002 Marco Pratesi (marco at telug dot it)

DOM = (document.getElementById) ? 1 : 0;
NS4 = (document.layers) ? 1 : 0;
// We need to explicitly detect Konqueror
// because Konqueror 3 sets IE = 1 ... AAAAAAAAAARGHHH!!!
Konqueror = (navigator.userAgent.indexOf("Konqueror") > -1) ? 1 : 0;
// We need to detect Konqueror 2.1 and 2.2 as they do not handle the window.onresize event
Konqueror21 = (navigator.userAgent.indexOf("Konqueror 2.1") > -1 || navigator.userAgent.indexOf("Konqueror/2.1") > -1) ? 1 : 0;
Konqueror22 = (navigator.userAgent.indexOf("Konqueror 2.2") > -1 || navigator.userAgent.indexOf("Konqueror/2.2") > -1) ? 1 : 0;
Konqueror2 = Konqueror21 || Konqueror22;
Opera5 = (navigator.userAgent.indexOf("Opera 5") > -1 || navigator.userAgent.indexOf("Opera/5") > -1) ? 1 : 0;
Opera6 = (navigator.userAgent.indexOf("Opera 6") > -1 || navigator.userAgent.indexOf("Opera/6") > -1) ? 1 : 0;
Opera = Opera5 || Opera6;
IE = (document.all) ? 1 : 0;
IE4 = IE && !DOM;
