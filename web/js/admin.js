

// сохранение настроек портала
var saveConfig = function() {
    var $frm = $('#form-for-config');
    
    $frm.validate();
        addFormValidationRules();
    if($frm.valid()) {
        var elements = $frm.serialize();
        var params = {
                    frm_data : elements               
            };
            $.ajax({
            url: global_data.baseURI +'/custom/save-configuration',
            type: 'POST',
            dataType : 'json',
            data: params,
            success: function(response) {
                $('<div class="center"><p>' + response.message +'</p>'+ global_data.close_button+'</div>').modal();
            }
        });
            
    } else {
        
    }
}

//Вспомогательные функции получения списка географических объектов
var getGeographyElements = (function() {
	return {
		getCities : function(regionId,$city_select){
                    $city_select.empty();
			console.log ('Город');
                        $.ajax({
                            url: global_data.baseURI + '/model/get-cities',
                            type: 'POST',
                        dataType: 'json',
                            data: {
                                 model : 'Region',
                                    id : regionId
                            },
                            success: function(cities_list) {                                
                                if ((cities_list.length) ==0) {
                                    return;
                                }
                                $.each(cities_list,function(i,val){
                                    $city_select.append($('<option>',{ value: val.Id }).text(val.Name));
                                });                                
                            }                            
                        });
			
		},
		getRegions : function(countryId,$region_select,$city_select){
                    var that = this;
                    $region_select.empty();
                    $city_select.empty();
			console.log ('Регион');
                    $.ajax({
                        url: global_data.baseURI + '/model/get-regions',
                        type: 'POST',
                    dataType: 'json',
                        data: {
                                 model : 'Country',
                                    id : countryId
                            },
                        success: function(regions_list) {
                            if ((regions_list.length) ==0) {
                                return;
                            }
                            var def_reg_id = regions_list[0].Id;
                            $.each(regions_list,function(i,val){
                                $region_select.append($('<option>',{ value: val.Id }).text(val.Name));
                            });
                            that.getCities( def_reg_id,$city_select );
                        }                            
                    });
		}
		
	}
})();
/* обработчик изменения региона и страны */
var changeGeographyElement = function(e) {
    var country_id,region_id;
    var $region_select = $('#regionId');
    var $city_select = $('#cityId');
    console.log($(e).val());
    switch (e.id) {
        case 'countryId':
            country_id = $(e).val();                        
            getGeographyElements.getRegions(country_id,$region_select,$city_select);
            break;
        case 'regionId':
            region_id = $(e).val();
            $('#cityId').empty();
            getGeographyElements.getCities(region_id,$city_select)
            break;
    }
}

$(function(){
/* выпадающее меню */
    var menuModules = $('.module ul.menu'),
        menuHeaders = $('.admin_menu .module');
    menuModules.addClass('hide');
    menuHeaders.mouseover(function(){
        $(this).children('ul').removeClass('hide');
    });
    menuHeaders.mouseout(function(){
        $(this).children('ul').addClass('hide');
    });

/* Менеджер модулей */
    // обработчик события "установка модуля"
    $('.available_modules').on('click','input[name="install"]',function(e) {
        var $mdl_name = $(this).closest('tr').attr('id');
        var ajaxData = {
                action : {
                    namespace : 'Rosagromash', 
                    className : 'Module',
                    method: 'installByName' },
                params : {
                    name : $mdl_name
                }
            };
        $.ajax({
                   url: global_data.ajaxURI,
                   type: 'GET',
                   data: ajaxData,
                   success: function(response) {
                        var status = $.parseJSON(response);
                            alert(status.text);
                            location.reload();
                   }
               });               
    });
    // обработчик события "удаление модуля"
    $('.installed_modules').on('click','input[name="delete"]',function(e) {
        var $mdl_name = $(this).closest('tr').attr('id');
                var ajaxData = {
                action : {
                    namespace : 'Rosagromash', 
                    className : 'Module',
                    method: 'uninstallByName' },
                params : {
                    name : $mdl_name
                }
            };
        $.ajax({
                   url: global_data.ajaxURI,
                   type: 'GET',
                   data: ajaxData,
                   success: function(response) {
                        var status = $.parseJSON(response);
                        alert(status.text);
                        location.reload();
                        
                   }
            });  
    });
   /* Контроль заполнения форм производителями */
   var formControl = $('#filterFormControl');
   if (formControl.length === 1) {
       var inputElements = $('#filterFormControl select').add('#filterFormControl input[type="checkbox"]');
       inputElements.on('change',function(){
           $('#fc-submit').trigger('click');
       });
       $('#excel-contractor').on('click',function(){
           var val = $('#contractor-types option:selected').val(),
           month = $('#select-month option:selected').val(),
           year = $('#select-year option:selected').val(),
           fltr = $('#inPortal').prop('checked') ? 1 : 0;
           window.location = global_data.baseURI + '/custom/get-excel-contractors?cat='+ val + '&month=' + month + '&year=' + year + '&fltr=' + fltr;                     
       });
   }
});