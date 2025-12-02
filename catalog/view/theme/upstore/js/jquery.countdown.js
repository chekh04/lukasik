(function($){
	function init(elem, options){
		elem.addClass('countdownHolder');
		$.each(['Days','Hours','Minutes','Sec'],function(i){
		var clas;
		if (this=='Days') {clas = chSetting.days}
		if (this=='Hours') {clas = chSetting.hours}
		if (this=='Minutes') {clas = chSetting.minutes}
		if (this=='Sec') {clas = chSetting.sec}
		out_timer = "";
		out_timer += '<span class="count'+this+'">';
		out_timer += '<span class="num-time">';
		if(this == 'Days'){
		out_timer += '<span class="position hidden"><span class="digit static">0</span></span>';
		}
		out_timer += '<span class="position"><span class="digit static">0</span></span>';
		out_timer += '<span class="position"><span class="digit static">0</span></span>';
		out_timer += '</span>';
		out_timer += '<span class="time_productany">'+clas+'</span>';
		out_timer += '</span>';
		$(out_timer).appendTo(elem);
		});
	}
	// Количество секунд в каждом временном отрезке
	var days	= 24*60*60,
		hours	= 60*60,
		minutes	= 60;

	// Создаем плагин
	$.fn.countdown = function(prop){

		var options = $.extend({
			callback	: function(){},
			timestamp	: 0
		},prop);

		var left, d, h, m, s, positions;

		// инициализируем плагин
		init(this, options);

		positions = this.find('.position');

		(function tick(){

			// Осталось времени
			left = Math.floor((options.timestamp - (new Date())) / 1000);

			if(left < 0){
				left = 0;
			}

			// Осталось дней
			d = Math.floor(left / days);
			if(d > 99){
				positions.removeClass('hidden');
			}
			updateDuo(0, 2, d);
			left -= d*days;

			// Осталось часов
			h = Math.floor(left / hours);
			updateDuo(3, 4, h);
			left -= h*hours;

			// Осталось минут
			m = Math.floor(left / minutes);
			updateDuo(5, 6, m);
			left -= m*minutes;

			// Осталось секунд
			s = left;
			updateDuo(7, 8, s);

			// Вызываем возвратную функцию пользователя
			options.callback(d, h, m, s);

			// Планируем следующий вызов данной функции через 1 секунду
			setTimeout(tick, 1000);
		})();

		// Данная функция обновляет две цифоровые позиции за один раз
		function updateDuo(minor,major,value){
			if(major - minor == 2) {
				switchDigit(positions.eq(0),Math.floor(value/10/10)%10);
				switchDigit(positions.eq(1),Math.floor(value/10)%10);
				switchDigit(positions.eq(2),value%10);
			} else {
				switchDigit(positions.eq(minor),Math.floor(value/10)%10);
				switchDigit(positions.eq(major),value%10);
			}
		}

		return this;
	};




	// Создаем анимированный переход между двумя цифрами
	function switchDigit(position,number){

		var digit = position.find('.digit')

		if(digit.is(':animated')){
			return false;
		}

		if(position.data('digit') == number){
			// Мы уже вывели данную цифру
			return false;
		}

		position.data('digit', number);

		var replacement = $('<span>',{
			'class':'digit',
			css:{

			},
			html:number
		});

		// Класс .static добавляется, когда завершается анимация.
		// Выполнение идет более плавно.

		digit
			.before(replacement)
			.removeClass('static')
			.animate({},'fast',function(){
				digit.remove();
			})

		replacement
			.delay(100)
			.animate({},'fast',function(){
				replacement.addClass('static');
			});
	}

})(jQuery);

function addTimer(){
	$('body .action-timer').each(function () {
		if($(this).children('.countDays').length == 0){
			if($(this).attr('data-date-end')){
				var parts_date = $(this).attr('data-date-end').split('-');
				var ts = new Date(parts_date[0], parts_date[1] - 1, parts_date[2]);
				if((new Date()) > ts){
					ts = (new Date()).getTime() + 10*24*60*60*1000;
				}
				$(this).countdown({
					timestamp	: ts,
					callback	: function(days, hours, minutes, seconds){
					var message = "";
					message += days;
					message += hours;
					message += minutes;
					message += seconds;
					$(this).html(message);
					}
				});
			}
		}
	});
}

$(function () {
	addTimer();
});