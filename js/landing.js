//Smooth scrolling with links
$('a[href*=\\#]').on('click', function(event){
    event.preventDefault();
    $('html,body').animate({scrollTop:$(this.hash).offset().top}, 500);
    var id = this.href.substr(this.href.indexOf('#')).replace('#');
    $(this).parent().siblings().removeClass('active');
    $(this).parent().addClass('active');
});
