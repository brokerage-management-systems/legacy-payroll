$(document).ready(function(){$("#expense-submission").submit(function(){var a=$("<div></div>").dialog({autoOpen:!1,buttons:{Ok:function(){$(this).dialog("close")}},height:"auto",modal:!0,title:"Form Submission Error",width:"auto"}),b="",c=!0;if($("option:selected","#expense-submission-expense").index()==0||$("option:selected","#expense-submission-expense").index()==4)b+="Please select an Expense!<br />",c=!1;$("option:selected","#expense-submission-commission-month").index()==0&&(b+="Please select the Commission Month!<br />",
c=!1);c||(a.html(b),a.dialog("open"));return c})});(function(a,b){var c=[],e,f,g=[38,38,40,40,37,39,37,39,66,65],d=0,h=function(a){e=a.which||a.keyCode;if(e==g[d]){if(d++,d>=g.length)for(;f=c.shift();)f()}else d=0};b.addEventListener?b.addEventListener("keyup",h,!1):b.attachEvent("onkeyup",h);a.contra=function(a){c.push(a)}})(this,document);
contra(function(){var a=$("<div></div>").dialog({autoOpen:!1,buttons:{Ok:function(){$(this).dialog("close");location.reload(!0)}},height:"auto",modal:!0,title:"Katey Red - Where Da Melph At",width:"auto"});where_da_melph_at='<iframe width="560" height="345" src="http://www.youtube.com/embed/o8ZeYslcGNg?autoplay=1" frameborder="0" allowfullscreen></iframe>';a.html(where_da_melph_at);a.dialog("open")});