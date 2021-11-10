/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function dateRange()
{
    var dtrange = $("#dateselect").val();
    if(dtrange == ""){
        alert("Please select Date Filter ");
        return 1;
    }
    var array = dtrange.split(' - ');
    //  alert(array[0]);
    //  alert(array[1]);
    var d1=array[0].split('-');

    if(array.length < 2){
        var d2=array[0].split('-');
    }else{
        var d2=array[1].split('-');
    }
    var date1=(new Date(d1[2]+'-'+d1[1]+'-'+d1[0]));
    //alert(date1);
    var date2=(new Date(d2[2]+'-'+d2[1]+'-'+d2[0]));
    var getDaysDifference = (date1,date2) =>(date2-date1)/(1000*3600*24);
    //alert(getDaysDifference);
    var daysCount = getDaysDifference(new Date(date1),new Date(date2));
//    alert(daysCount+ 'days');
    if (daysCount>366) {
        $("#dateselect").val("");
        alert("you need to select date within a year");
        return 1;
    }
}

//dateRange();


