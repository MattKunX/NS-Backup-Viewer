/**
 * Created by mattkun on 7/18/2016.
 */
$('.tab-header h3').on('click',function(){
    $('.active').toggleClass('active');
    $(this).toggleClass('active');
    $('.visible').toggleClass('visible');
    $('.'+this.id).toggleClass('visible');
});

function numberWithCommas(number) {
    var parts = number.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}

$('.field div').each(function() {
	var val = $(this).text();
	if(val.indexOf('.') != -1){
		var commaNum = numberWithCommas(val);
		$(this).text(commaNum);
	}
});
