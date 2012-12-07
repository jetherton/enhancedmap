/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2011-11-15
 * Purpose:	   Java Script used by the big map to add dragging and minizing functionatliy to the timeline and category
 *             filters dialog boxes
 * Inputs:     N/A
 * Outputs:    Java Script
 *
 * The Enhanced Map, Ushahidi Plugin is free software: you can redistribute
 * it and/or modify it under the terms of the GNU Lesser General Public License
 * as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The Enhanced Map, Ushahidi Plugin is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with the Enhanced Map, Ushahidi Plugin.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * Changelog:
 * 2011-11-15:  Etherton - Initial release
 *
 * Developed by Etherton Technologies Ltd.
 */





//for toggling the windows on and off
function togglelayer(objectID, changeID) {
    var theElementStyle = document.getElementById(objectID);
    var theChangedElement = document.getElementById(changeID);

    if(theElementStyle.style.display == "none") {
        theElementStyle.style.display = "block";
        theChangedElement.innerHTML = "-";
        
	//if it's the timeline, redraw it
	if(objectID == "timeline_colapse")
	{
		var startDate = $("#startDate").val();
		var endDate = $("#endDate").val();
		refreshGraph(startDate, endDate);
	}
	
	
    }
    else {
        theElementStyle.style.display = "none";
        theChangedElement.innerHTML = "+";
	
	
    }
}


var lastPanOnResizeDirection = 1;
var dragObject, offsetX, offsetY, isDragging=false;
window.onload = init;
document.onmousemove = mM;
document.onmouseup = mU;

//init things so we can drag things on screen
function init() {

	//capture resize events on the map to make sure it's redrawn properly
	$(window).resize(function() {
  			map.updateSize();
  			if(lastPanOnResizeDirection == 1)
  			{
				map.pan(0,1);
				lastPanOnResizeDirection = 0
			}
			else
			{
				map.pan(0,-1);
				lastPanOnResizeDirection = 1
			}
		});
	
	var myWidth = 0, myHeight = 0;
	if( typeof( window.innerWidth ) == 'number' ) {
		//Non-IE
		myWidth = window.innerWidth;
		myHeight = window.innerHeight;
	} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		//IE 6+ in 'standards compliant mode'
		myWidth = document.documentElement.clientWidth;
		myHeight = document.documentElement.clientHeight;
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
	//IE 4 compatible
		myWidth = document.body.clientWidth;
		myHeight = document.body.clientHeight;
	}
	
	
	
	
	
	
	
	
	ob = document.getElementById("right");
	ob.style.display="block";
	ob.style.left=(myWidth - (250 + 10)) + "px";
	ob.style.top="150px";
	ob.ondrag=function(){return false;};
	ob.onselectstart=function(){return false;};
	
	ob = document.getElementById("timeline_holder");
	ob.style.left="1px";
	ob.style.top= myHeight - (230 + 1) + "px";
	ob.style.display="block";
	ob.ondrag=function(){return false;};
	ob.onselectstart=function(){return false;};
}

function mD(ob,e) {
	dragObject = ob.parentNode;
	if (window.event) e=window.event;
	
	var dragX = parseInt(dragObject.style.left);
	var dragY = parseInt(dragObject.style.top);
	
	var mouseX = e.clientX;
	var mouseY = e.clientY;
	
	offsetX = mouseX - dragX;
	offsetY = mouseY - dragY;
	
	isDragging = true;
	
	return false;
}

function mM(e) {
	if (!isDragging) return;
	
	if (window.event) e=window.event;
	
	var newX = e.clientX - offsetX;
	var newY = e.clientY - offsetY;
	
	dragObject.style.left = newX + "px";
	dragObject.style.top = newY + "px";
	return false;
}

function mU() {
	if (!isDragging) return;
	
	isDragging = false;
	
	return false;
}

