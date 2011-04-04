var scroller;

document.addEventListener('DOMContentLoaded', loaded);

$(function() {

});

function on_load()
{
  $('#preloader').hide();
}

function loaded() {
	document.addEventListener('touchmove', function(e){ e.preventDefault(); });
	scroller = new iScroll('infographic',{ snap:false, momentum:true, hScrollbar:true, vScrollbar:false });
	scroller.scrollToElement("#item-0", '0ms');
}

