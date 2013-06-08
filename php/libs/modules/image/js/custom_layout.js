(function($) {
	$(document).ready(function() {
		draw_grid();
        $('#width').keyup(function(){
            draw_grid();
        })
        $('#height').keyup(function(){
            draw_grid();
        })
        $('#type').change(function(){
            var type = $(this).val();
            var url = '/image/get_types/~/' + type;
            var options = {
                url: url,
                dataType: 'json'
            };
            $.ajax(options).done(function(data){
                console.log(data);
                $('#height').val(data.height);
                $('#width').val(data.width);
                draw_grid();
            });
        })
	});
})(jQuery);
var imagecount = 0;
function draw_grid(selected){
    var w = parseInt($('#width').val());
    var h = parseInt($('#height').val());
    var factors = common_factors(w,h);
    var buttons = render_button_grp(factors);
    $('.grid-size-buttons').html(buttons);
    $('.grid-size-buttons button').click(function(){
        draw_grid($(this).html());
    })
    if(selected){
        $('.grid-size-buttons button').removeClass('active');
        $('.grid-size-buttons #' + selected).addClass('active');
    }
    var selected = $('.grid-size-buttons .active').html();
    
    var cols = w / selected;
    var rows = h / selected;
    var wsize = 100/cols;
    var hsize = 100/rows;

    var aratio = h/w;

    html = $('<ol id="grid"></ol>');
    $('.grid-container').html(html);
    for(var r=0;r<rows;r++){
        for(var c=0;c<cols;c++){
            $('.grid-container #grid').append('<li id="' + r +'-'+c+'" style="width:'+wsize+'%; height:'+hsize+'%;"></li>'); 
        }
    }

    if(($('.grid-container ol').width()*.75) > w){
        $('.grid-container ol').width(w+'px');
    }

    $('.grid-container ol').height($('.grid-container ol').width()*aratio);

    $( ".grid-container ol" ).selectable({
        stop: function(){
            var selection = [];
            $( ".grid-container #grid li.ui-selected" ).each(function(){
                selection.push($(this).attr('id'));
            });

            if(isSquare(selection)){
                $('.grid-container #grid li.ui-selected').attr('image-id', imagecount).css('background-color', getRandomColor());
                imagecount++;
                validateImages();
            }else{
                
            }  
        },
        unselecting:function( event, ui ) {
            var selection = [];
            $( ".grid-container #grid li.ui-selecting" ).each(function(){
                selection.push($(this).attr('id'));
            });
            if(isSquare(selection)){
                $('.grid-container #grid li.ui-bad-selection').removeClass('ui-bad-selection');
            }else{
                $('.grid-container #grid li.ui-selecting').addClass('ui-bad-selection');
            } 
        },
        selecting: function( event, ui ) {
            var selection = [];
            $( ".grid-container #grid li.ui-selecting" ).each(function(){
                selection.push($(this).attr('id'));
            });
            if(isSquare(selection)){
                $('.grid-container #grid li.ui-bad-selection').removeClass('ui-bad-selection');
            }else{
                $('.grid-container #grid li.ui-selecting').addClass('ui-bad-selection');
            } 
        }
    });
}

function isSquare(selection){
    if(selection.length == 0){
        return false;
    }
    //if its square then the length of the array will be double the size of a size;
    var home = selection[0].split('-');
    var side = Math.sqrt(selection.length) - 1;
    var end = (parseInt(home[0]) + side) + '-' + (parseInt(home[1]) + side);
    var square = selection.indexOf(end);
    if(square >= 0){
        return true;
    }else{
        return false;
    }
}

function validateImages(){

    for(var i = 0; i<imagecount;i++){
        var selection = [];
        var image =  $('[image-id="'+i+'"]');
        $(image).each(function(){
            selection.push($(this).attr('id'));
        });
        if(!isSquare(selection)){
            $(image).each(function(){
                $(this).attr('image-id', '').css('background-color', '');
            });
        }
    }

}

function render_button_grp(arry){
    var selected = (arry.length-1);
    var html = '<strong>Grid Size</strong><br/><div class="btn-group">';
    for(var i=0;i<arry.length;i++){
        var clss = '';
        if(i == selected){
            clss = 'active';
        }
        html += '<button class="btn '+clss+'" id="'+arry[i]+'">' + arry[i] + '</button>';
    }
    html += '</div>';
    return html;
}

function common_factors(w,h){
    var w_fac = find_factors(w);
    var h_fac = find_factors(h);
    var f = array_intersect(w_fac, h_fac);
    
    var gcf = f[f.length-1];
    ret = [];
    ret.push(gcf);
    while(gcf>w/10){
        gcf = gcf/2;
        ret.push(gcf);
    }
    return ret; 
    
}

function find_factors(n){
    var factors = [];
    for(var i=0; i<=n;i++){
        if((n%i == 0) || (n/i == 1)){
            factors.push(i);
        }

    }
    return factors;
}

function array_intersect (arr1) {
  var retArr = {},
    argl = arguments.length,
    arglm1 = argl - 1,
    k1 = '',
    arr = {},
    i = 0,
    k = '';

  arr1keys: for (k1 in arr1) {
    arrs: for (i = 1; i < argl; i++) {
      arr = arguments[i];
      for (k in arr) {
        if (arr[k] === arr1[k1]) {
          if (i === arglm1) {
            retArr[k1] = arr1[k1];
          }
          // If the innermost loop always leads at least once to an equal value, continue the loop until done
          continue arrs;
        }
      }
      // If it reaches here, it wasn't found in at least one array, so try next value
      continue arr1keys;
    }
  }

  //this shitty but
  var ret = [];
  for (v in retArr){
    ret.push(retArr[v]);
  }
  return ret;
}

function getRandomColor() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.round(Math.random() * 15)];
    }
    return color;
}