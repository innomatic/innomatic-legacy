// PHP Layers Menu 2.3.5 (C) 2001-2003 Marco Pratesi (marco at telug dot it)

numl = 0;

DOM = (document.getElementById) ? 1 : 0;
NS4 = (document.layers) ? 1 : 0;
// We need to explicitly detect Konqueror
// because Konqueror 3 sets IE = 1 ... AAAAAAAAAARGHHH!!!
Konqueror = (navigator.userAgent.indexOf("Konqueror") > -1) ? 1 : 0;
// We need to detect Konqueror 2.1 and 2.2 as they do not handle the window.onresize event
Konqueror21 = (navigator.userAgent.indexOf("Konqueror 2.1") > -1 || navigator.userAgent.indexOf("Konqueror/2.1") > -1) ? 1 : 0;
Konqueror22 = (navigator.userAgent.indexOf("Konqueror 2.2") > -1 || navigator.userAgent.indexOf("Konqueror/2.2") > -1) ? 1 : 0;
Konqueror2 = Konqueror21 || Konqueror22;
Opera = (navigator.userAgent.indexOf("Opera") > -1) ? 1 : 0;
Opera5 = (navigator.userAgent.indexOf("Opera 5") > -1 || navigator.userAgent.indexOf("Opera/5") > -1) ? 1 : 0;
Opera6 = (navigator.userAgent.indexOf("Opera 6") > -1 || navigator.userAgent.indexOf("Opera/6") > -1) ? 1 : 0;
Opera56 = Opera5 || Opera6;
IE = (document.all) ? 1 : 0;
IE4 = IE && !DOM;

// PHP Layers Menu 2.3.5 (C) 2001-2003 Marco Pratesi (marco at telug dot it)

function setVisibility(layer,on) {
    if (on) {
        if (DOM) {
            document.getElementById(layer).style.visibility = "visible";
        } else if (NS4) {
            document.layers[layer].visibility = "show";
        } else {
            document.all[layer].style.visibility = "visible";
        }
    } else {
        if (DOM) {
            document.getElementById(layer).style.visibility = "hidden";
        } else if (NS4) {
            document.layers[layer].visibility = "hide";
        } else {
            document.all[layer].style.visibility = "hidden";
        }
    }
}

function isVisible(layer) {
    if (DOM) {
        return (document.getElementById(layer).style.visibility == "visible");
    } else if (NS4) {
        return (document.layers[layer].visibility == "show");
    } else {
        return (document.all[layer].style.visibility == "visible");
    }
}

function setLeft(layer,x) {
    if (DOM && !Opera5) {
        document.getElementById(layer).style.left = x + "px";
    } else if (Opera5) {
        document.getElementById(layer).style.left = x;
    } else if (NS4) {
        document.layers[layer].left = x;
    } else {
        document.all[layer].style.pixelLeft = x;
    }
}

function getOffsetLeft(layer) {
    var value = 0;
    if (DOM) {    // Mozilla, Konqueror >= 2.2, Opera >= 5, IE
            // timing problems with Konqueror 2.1 ?
        object = document.getElementById(layer);
        value = object.offsetLeft;
//alert (object.tagName + " --- " + object.offsetLeft);
        while (object.tagName != "BODY" && object.offsetParent) {
            object = object.offsetParent;
//alert (object.tagName + " --- " + object.offsetLeft);
            value += object.offsetLeft;
        }
    } else if (NS4) {
        value = document.layers[layer].pageX;
    } else {    // IE4 IS SIMPLY A BASTARD !!!
        if (document.all["IE4" + layer]) {
            layer = "IE4" + layer;
        }
        object = document.all[layer];
        value = object.offsetLeft;
        while (object.tagName != "BODY") {
            object = object.offsetParent;
            value += object.offsetLeft;
        }
    }
    return (value);
}

function setTop(layer,y) {
    if (DOM && !Opera5) {
        document.getElementById(layer).style.top = y + "px";
    } else if (Opera5) {
        document.getElementById(layer).style.top = y;
    } else if (NS4) {
        document.layers[layer].top = y;
    } else {
        document.all[layer].style.pixelTop = y;
    }
}

function getOffsetTop(layer) {
// IE 5.5 and 6.0 behaviour with this function is really strange:
// in some cases, they return a really too large value...
// ... after all, IE is buggy, nothing new
    var value = 0;
    if (DOM) {
        object = document.getElementById(layer);
        value = object.offsetTop;
        while (object.tagName != "BODY" && object.offsetParent) {
            object = object.offsetParent;
            value += object.offsetTop;
        }
    } else if (NS4) {
        value = document.layers[layer].pageY;
    } else {    // IE4 IS SIMPLY A BASTARD !!!
        if (document.all["IE4" + layer]) {
            layer = "IE4" + layer;
        }
        object = document.all[layer];
        value = object.offsetTop;
        while (object.tagName != "BODY") {
            object = object.offsetParent;
            value += object.offsetTop;
        }
    }
    return (value);
}

function setWidth(layer,w) {
    if (DOM) {
        document.getElementById(layer).style.width = w;
    } else if (NS4) {
//        document.layers[layer].width = w;
    } else {
        document.all[layer].style.pixelWidth = w;
    }
}

function getOffsetWidth(layer) {
    var value = 0;
    if (DOM && !Opera56) {
        value = document.getElementById(layer).offsetWidth;
        if (isNaN(value)) {
        // e.g. undefined on Konqueror 2.1
            if (abscissaStep) {    // this variable is set if this function is used with the PHP Layers Menu System
                value = abscissaStep;
            } else {
                value = 0;
            }
        }
    } else if (NS4) {
        value = document.layers[layer].document.width;
    } else if (Opera56) {
        value = document.getElementById(layer).style.pixelWidth;
    } else {    // IE4 IS SIMPLY A BASTARD !!!
        if (document.all["IE4" + layer]) {
            layer = "IE4" + layer;
        }
        value = document.all[layer].offsetWidth;
    }
    return (value);
}

function setHeight(layer,h) {    // unused, not tested
    if (DOM) {
        document.getElementById(layer).style.height = h;
    } else if (NS4) {
//        document.layers[layer].height = h;
    } else {
        document.all[layer].style.pixelHeight = h;
    }
}

function getOffsetHeight(layer) {
    var value = 0;
    if (DOM && !Opera56) {
        value = document.getElementById(layer).offsetHeight;
        if (isNaN(value)) {
        // e.g. undefined on Konqueror 2.1
            value = 25;
        }
    } else if (NS4) {
        value = document.layers[layer].document.height;
    } else if (Opera56) {
        value = document.getElementById(layer).style.pixelHeight;
    } else {    // IE4 IS SIMPLY A BASTARD !!!
        if (document.all["IE4" + layer]) {
            layer = "IE4" + layer;
        }
        value = document.all[layer].offsetHeight;
    }
    return (value);
}

function getWindowWidth() {
    var value = 0;
    if ((DOM && !IE) || NS4 || Konqueror || Opera) {
        value = top.innerWidth;
//    } else if (NS4) {
//        value = document.width;
    } else {    // IE
        if (document.documentElement && document.documentElement.clientWidth) {
            value = document.documentElement.clientWidth;
        } else if (document.body) {
            value = document.body.clientWidth;
        }
    }
    if (isNaN(value)) {
        value = top.innerWidth;
    }
    return (value);
}

function getWindowXOffset() {
    var value = 0;
    if ((DOM && !IE) || NS4 || Konqueror || Opera) {
        value = window.pageXOffset;
    } else {    // IE
        if (document.documentElement && document.documentElement.scrollLeft) {
            value = document.documentElement.scrollLeft;
        } else if (document.body) {
            value = document.body.scrollLeft;
        }
    }
    return (value);
}

function getWindowHeight() {
    var value = 0;
    if ((DOM && !IE) || NS4 || Konqueror || Opera) {
        value = top.innerHeight;
    } else {    // IE
        if (document.documentElement && document.documentElement.clientHeight) {
            value = document.documentElement.clientHeight;
        } else if (document.body) {
            value = document.body.clientHeight;
        }
    }
    if (isNaN(value)) {
        value = top.innerHeight;
    }
    return (value);
}

function getWindowYOffset() {
    var value = 0;
    if ((DOM && !IE) || NS4 || Konqueror || Opera) {
        value = window.pageYOffset;
    } else {    // IE
        if (document.documentElement && document.documentElement.scrollTop) {
            value = document.documentElement.scrollTop;
        } else if (document.body) {
            value = document.body.scrollTop;
        }
    }
    return (value);
}

// PHP Layers Menu 2.3.5 (C) 2001-2003 Marco Pratesi (marco at telug dot it)

loaded = 0;
layersMoved = 0;

menuLeftShift = 6;
menuRightShift = 10;

layerPoppedUp = "";

currentY = 0;
function grabMouse(e) {    // for NS4
    currentY = e.pageY;
}
if (NS4) {
    document.captureEvents(Event.MOUSEDOWN | Event.MOUSEMOVE);
    document.onmousemove = grabMouse;
}

function shutdown() {
    for (i=1; i<=numl; i++) {
        LMPopUpL(listl[i], false);
    }
    layerPoppedUp = "";
}

function none()
{
}

function handle_shutdown()
{
if (NS4) {
    document.onmousedown = shutdown;
} else {
    document.onclick = shutdown;
}
}

function unhandle_shutdown()
{
if (NS4) {
    document.onmousedown = none;
} else {
    document.onclick = none;
}
}

function moveLayerX(menuName) {
    if (!loaded || (isVisible(menuName) && menuName != layerPoppedUp)) {
        return;
    }
    if (father[menuName] != "") {
        if (!Opera5 && !IE4) {
            width0 = lwidth[father[menuName]];
            width1 = lwidth[menuName];
        } else if (Opera5) {
            // Opera 5 stupidly and exaggeratedly overestimates layers widths
            // hence we consider a default value equal to $abscissaStep
            width0 = abscissaStep;
            width1 = abscissaStep;
        } else if (IE4) {
            width0 = getOffsetWidth(father[menuName]);
            width1 = getOffsetWidth(menuName);
        }
        onLeft = getOffsetLeft(father[menuName]) - width1 + menuLeftShift;
        onRight = getOffsetLeft(father[menuName]) + width0 - menuRightShift;
        windowWidth = getWindowWidth();
        windowXOffset = getWindowXOffset();
//        if (NS4 && !DOM) {
//            windowXOffset = 0;
//        }
        if (onLeft < windowXOffset && onRight + width1 > windowWidth + windowXOffset) {
            if (onRight + width1 - windowWidth - windowXOffset > windowXOffset - onLeft) {
                onLeft = windowXOffset;
            } else {
                onRight = windowWidth + windowXOffset - width1;
            }
        }
        if (back[father[menuName]]) {
            if (onLeft < windowXOffset) {
                back[menuName] = 0;
            } else {
                back[menuName] = 1;
            }
        } else {
//alert(onRight + " - " + width1 + " - " +  windowWidth + " - " + windowXOffset);
            if (onRight + width1 > windowWidth + windowXOffset) {
                back[menuName] = 1;
            } else {
                back[menuName] = 0;
            }
        }
        if (back[menuName]) {
            setLeft(menuName, onLeft);
        } else {
            setLeft(menuName, onRight);
        }
    }
    moveLayerY(menuName, 0);    // workaround needed for Mozilla for MS Windows
}

function moveLayerY(menuName, ordinateMargin) {
    if (!loaded || (isVisible(menuName) && menuName != layerPoppedUp)) {
        return;
    }
    if (!layersMoved) {
        moveLayers();
        layersMoved = 1;
    }
    if (!NS4) {
        newY = getOffsetTop("ref" + menuName);
    } else {
        newY = currentY;
    }
    newY -= ordinateMargin;
    layerHeight = getOffsetHeight(menuName);
    windowHeight = getWindowHeight();
    windowYOffset = getWindowYOffset();
    if (newY + layerHeight > windowHeight + windowYOffset) {
        if (layerHeight > windowHeight) {
            newY = windowYOffset;
        } else {
            newY = windowHeight + windowYOffset - layerHeight;
        }
    }
    if (Math.abs(getOffsetTop(menuName) - newY) > thresholdY) {
        setTop(menuName, newY);
    }
}

function LMPopUpRoot(menuName, isCurrent) {
unhandle_shutdown();
    if (!loaded || menuName == layerPoppedUp || (isVisible(menuName) && !isCurrent)) {
        return;
    }
    if (menuName == father[layerPoppedUp]) {
        LMPopUpL(layerPoppedUp, false);
    } else if (father[menuName] == layerPoppedUp) {
        LMPopUpL(menuName, true);
    } else {
        shutdown();
        foobar = menuName;
        do {
            LMPopUpL(foobar, true);
            foobar = father[foobar];
        } while (foobar != "")
    }
    layerPoppedUp = menuName;

    setTimeout( 'handle_shutdown()', 100 );
}

function LMPopUp(menuName, isCurrent) {
    if (!loaded || menuName == layerPoppedUp || (isVisible(menuName) && !isCurrent)) {
        return;
    }
    if (menuName == father[layerPoppedUp]) {
        LMPopUpL(layerPoppedUp, false);
    } else if (father[menuName] == layerPoppedUp) {
        LMPopUpL(menuName, true);
    } else {
        shutdown();
        foobar = menuName;
        do {
            LMPopUpL(foobar, true);
            foobar = father[foobar];
        } while (foobar != "")
    }
    layerPoppedUp = menuName;
}

function LMPopUpL(menuName, on) {
    if (!loaded) {
        return;
    }
    if (!layersMoved) {
        moveLayers();
        layersMoved = 1;
    }
    setVisibility(menuName, on);
}

function resizeHandler() {
    if (NS4) {
        window.location.reload();
    }
    shutdown();
    for (i=1; i<=numl; i++) {
        setLeft(listl[i], 0);
        setTop(listl[i], 0);
    }
//    moveLayers();
    layersMoved = 0;
}
window.onresize = resizeHandler;

function yaresizeHandler() {
    if (window.innerWidth != origWidth || window.innerHeight != origHeight) {
        if (Konqueror2 || Opera5) {
            window.location.reload();    // Opera 5 often fails this
        }
        origWidth  = window.innerWidth;
        origHeight = window.innerHeight;
        resizeHandler();
    }
    setTimeout('yaresizeHandler()', 500);
}
function loadHandler() {
    if (Konqueror2 || Opera56) {
        origWidth  = window.innerWidth;
        origHeight = window.innerHeight;
        yaresizeHandler();
    }
}
window.onload = loadHandler;

function fixieflm(menuName) {
    if (DOM) {
        setWidth(menuName, "100%");
    } else {    // IE4 IS SIMPLY A BASTARD !!!
        document.write("</div>");
        document.write("<div id=\"IE4" + menuName + "\" style=\"position: relative; width: 100%; visibility: visible;\">");
    }
}

// PHP Layers Menu 2.3.5 (C) 2001-2003 Marco Pratesi (marco at telug dot it)

function setLMCookie(name, value) {
    document.cookie = name + "=" + value;
}

function getLMCookie(name) {
    foobar = document.cookie.split(name + "=");
    if (foobar.length < 2) {
        return null;
    }
    tempString = foobar[1];
    if (tempString.indexOf(";") == -1) {
        return tempString;
    }
    yafoobar = tempString.split(";");
    return yafoobar[0];
}

function parseExpandString() {
    expandString = getLMCookie("expand");
    expand = new Array();
    if (expandString) {
        expanded = expandString.split("|");
        for (i=0; i<expanded.length-1; i++) {
            expand[expanded[i]] = 1;
        }
    }
}

function parseCollapseString() {
    collapseString = getLMCookie("collapse");
    collapse = new Array();
    if (collapseString) {
        collapsed = collapseString.split("|");
        for (i=0; i<collapsed.length-1; i++) {
            collapse[collapsed[i]] = 1;
        }
    }
}

parseExpandString();
parseCollapseString();

function saveExpandString() {
    expandString = "";
    for (i=0; i<expand.length; i++) {
        if (expand[i] == 1) {
            expandString += i + "|";
        }
    }
    setLMCookie("expand", expandString);
}

function saveCollapseString() {
    collapseString = "";
    for (i=0; i<collapse.length; i++) {
        if (collapse[i] == 1) {
            collapseString += i + "|";
        }
    }
    setLMCookie("collapse", collapseString);
}
