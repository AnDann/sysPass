var gsorder = 0;
var lastlen = 0;

var order = {};
order.key = 0;
order.dir = 0;

var strPassword;
var charPassword;
var minPasswordLength = 8;
var baseScore = 0, score = 0;

var num = {};
num.Excess = 0;
num.Upper = 0;
num.Numbers = 0;
num.Symbols = 0;

var bonus = {};
bonus.Excess = 3;
bonus.Upper = 4;
bonus.Numbers = 5;
bonus.Symbols = 5;
bonus.Combo = 0;
bonus.FlatLower = 0;
bonus.FlatNumber = 0;

var powertipOptions = {placement: 'ne', smartPlacement: 'true', fadeOutTime: 500};

jQuery.extend(jQuery.fancybox.defaults, {
    type: 'ajax',
    autoWidth: 'true',
    autoHeight: 'true',
    minHeight: 50,
    padding: 0,
    helpers:  {overlay : { css : { 'background' : 'rgba(0, 0, 0, 0.1)'}}},
    afterShow: function(){$('#fancyContainer input:visible:first').focus();}
});

$(document).ready(function(){
    $("[title]").powerTip(powertipOptions);
    $('input, textarea').placeholder();
    setContentSize();
}).ajaxComplete(function() {
    $("[title]").powerTip(powertipOptions);
    $('input, textarea').placeholder();
});

function doAction(action, lastAction, id){
    var data = {'action' : action,'lastaction': lastAction,'id': id, is_ajax: 1};
    
    $.fancybox.showLoading();

    $.ajax({
        type: 'POST',
        dataType: 'html',
        url: APP_ROOT + '/ajax/ajax_getcontent.php',
        data: data,        
        success: function(response){
            $('#content').html(response);
            setContentSize();
        },
        error:function(){$('#content').html(resMsg("nofancyerror"));},
        complete: function(){$.fancybox.hideLoading();}
    });
}

function setContentSize(){
    // Calculate total height for full body resize
    var totalHeight = $("#content").height() + 100;
    var totalWidth = $("#wrap").width();
    
//    alert(totalWidth + 'x' + totalHeight);
    $("#container").css("height",totalHeight);
//    $("#wrap").css("width",totalWidth);
}

function scrollUp(){
    $('html, body').animate({ scrollTop: 0 }, 'slow');
}

// Función para limpiar un formulario
function Clear(id, search){
    $("#" + id).resetForm();
    
    if ( search == 1 ){
        document.frmSearch.search.value = "";
        document.frmSearch.customer.selectedIndex = 0;
        document.frmSearch.category.selectedIndex = 0;
        $('#frmSearch input[name="start"]').val(0);
        $('#frmSearch input[name="skey"]').val(0);
        $('#frmSearch input[name="sorder"]').val('ASC');
    }
}

// Función para realizar una búsqueda
function accSearch(continous){
    var lenTxtSearch = $('#txtSearch').val().length;
   
    if ( lenTxtSearch < 3 && continous === 1 && lenTxtSearch >  window.lastlen ) return;
   
    window.lastlen = lenTxtSearch;
    
    var datos = $("#frmSearch").serialize();
    $.fancybox.showLoading();

    $.ajax({
        type: 'POST',
        dataType: 'html',
        url: APP_ROOT + '/ajax/ajax_search.php',
        data: datos,
        success: function(response){
            $('#resBuscar').html(response);
            $('#data-search').css("max-height",$('html').height() - 300);
        },
        error:function(){$('#resBuscar').html(resMsg("nofancyerror"));},
        complete: function(){$.fancybox.hideLoading();}
    });
    return false;
}

// Función para buscar con la ordenación por campos
function searchSort(skey,start,nav){
    if ( typeof(skey) == "undefined" || typeof(start) == "undefined" ) return false
   
    if ( order.dir === 0 ){
        if ( nav === 1 ){
            var sorder = 0;
        } else {
            if ( order.key > 0 && order.key != skey ){
                order.key = skey;
                var sorder = 0;
            } else{
                order.key = skey;
                order.dir = 1;
                var sorder = 1;
            }
        }
    } else {
        if ( nav === 1 ){
            var sorder = 1;
        } else {
            if ( order.key > 0 && order.key != skey ){
                order.key = skey;
                var sorder = 1;
            } else{
                order.key = skey;
                order.dir = 0;
                var sorder = 0;
            }
        }
    }
    
    $('#frmSearch input[name="skey"]').val(skey);
    $('#frmSearch input[name="sorder"]').val(sorder);
    $('#frmSearch input[name="start"]').val(start);
    
    var frmData = $("#frmSearch").serialize();

    $.fancybox.showLoading();

    $.ajax({
        type: 'POST',
        dataType: 'html',
        url: APP_ROOT + '/ajax/ajax_search.php',
        data: frmData,
        success: function(response){
            $('#resBuscar').html(response);
            $('#data-search').css("max-height",$('html').height() - 300);
        },
        error:function(){$('#resBuscar').html(resMsg("nofancyerror"));},
        complete: function(){
            scrollUp();
            $.fancybox.hideLoading();
        }
    });
}

// Función para buscar con la ordenación por campos
function navLog(start, current){
    if ( typeof(start) === "undefined" ) return false
    
    $.fancybox.showLoading();

    $.ajax({
        type: 'POST',
        dataType: 'html',
        url: APP_ROOT + '/ajax/ajax_eventlog.php',
        data: {'start' : start, 'current' : current},
        success: function(response){
            $('#content').html(response);
        },
        error:function(){$('#content').html(resMsg("nofancyerror"));},
        complete: function(){
            $.fancybox.hideLoading();
            scrollUp();
            setContentSize();
        }
    });
}

// Función para ver la clave de una cuenta
function viewPass(id,full,history){
    $.post( APP_ROOT + '/ajax/ajax_viewpass.php',
       {'accountid': id, 'full': full, 'isHistory' : history},
        function( data ) {
            if ( data.length === 0 ){
                doLogout();
            } else {
                resMsg("none",data);
            }
        }
    );
}

// Función para las variables de la URL y parsearlas a un array.
function getUrlVars(){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++){
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

// Función para autentificar usuarios
function doLogin(){
    $.fancybox.showLoading();

    var form_data = {user: $("#user").val(), pass: $("#pass").val(), mpass: $("#mpass").val(), login: 'login', is_ajax: 1};
    
    $("#btnLogin").prop('disabled',true);
    
    $.ajax({
        type: "POST",
        dataType: "xml",
        url: APP_ROOT + '/ajax/ajax_doLogin.php',
        data: form_data,
        success: function(xml){            
            var status = parseInt($(xml).find("status").text());
            var description = $(xml).find("description").text();
            
            if( status === 0 || status === 2 ){
                location.href = 'index.php';
            } else if ( status === 3 || status === 4 ){
                resMsg("error", description);
                $("#mpass").prop('disabled',false);
                $('#smpass').show();
            } else if ( status === 5 ){
                resMsg("warn", description,'',"location.href = 'index.php';");
            } else {
                resMsg("error", description);
            }
        },
        complete: function(){$('#btnLogin').prop('disabled',false); $.fancybox.hideLoading();},
        statusCode: {
            404: function() {
            var txt = LANG[1] + '<p>' + LANG[22] + '</p>';
            resMsg("error", txt);
        }},
    });
    
    return false;
}

function doLogout() {
    location.href = 'index.php?logout=1';
}

function checkLogout(){
    var session = getUrlVars()["session"];

    if ( session == 0 ){
        resMsg("warn", LANG[2],'',"location.search = ''");
    }
}

// Función para añadir/editar una cuenta
function saveAccount(frm) {
    var data = $("#"+frm).serialize();
    var id = $('input[name="accountid"]').val();
    var savetyp = $('input[name="savetyp"]').val();
    var action = $('input[name="next"]').val();

    switch(savetyp){
        case "1":
            break;
        case "2": 
            break;
    }
    
    $('#btnGuardar').attr('disabled', true);
    $.fancybox.showLoading();

    $.ajax({
        type: 'POST',
        dataType: 'xml',
        url: APP_ROOT + '/ajax/ajax_accountsave.php',
        data: data,
        success: function(xml){
            var status = parseInt($(xml).find("status").text());
            var description = $(xml).find("description").text();
                        
            if ( status === 0 ){                
                resMsg("ok", description);
                
                if ( savetyp == 1 ){
                    $('#btnSave').hide();
                } else{
                    $('#btnSave').attr('disabled', true);
                }
                
                if ( action && id ){
                    doAction(action,'accsearch',id);
                }
            } else if ( status === 10){
                doLogout();
            } else {
                resMsg("error", description);
                $('#btnSave').removeAttr("disabled");						
            }
        },
        error:function(jqXHR, textStatus, errorThrown){
            var txt = LANG[1] + '<p>' + errorThrown + textStatus + '</p>';
            resMsg("error", txt);
        },
        complete: function(){$.fancybox.hideLoading();}
    });
}

// Función para eliminar una cuenta
function delAccount(id,action,sk){
    var data = {accountid: id, savetyp: action, sk: sk};
    var atext = '<div id="alert"><p id="alert-text">' + LANG[8] + '</p></div>';
    
    alertify.confirm(atext, function (e) {
        if (e) {
            $.fancybox.showLoading();
            $.ajax({
                type: 'POST',
                dataType: 'xml',
                url: APP_ROOT + '/ajax/ajax_accountsave.php',
                data: data,
                success: function(xml){
                    var status = parseInt($(xml).find("status").text());
                    var description = $(xml).find("description").text();

                    if ( status === 0 ){
                        resMsg("ok", description);
                        doAction('accsearch');
                    } else if ( status === 10){
                        resMsg("error", description);
                        doLogout();
                    } else {
                        resMsg("error", description);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){ 
                    resMsg("error", 'Oops...' + LANG[0]);
                },
                complete: function(){$.fancybox.hideLoading();}
            });
        }
    });
}

// Función para enviar una solicitud de modificación de cuenta
function sendRequest(){
    var data = $('#frmRequestModify').serialize();
    
    $.fancybox.showLoading();
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: APP_ROOT + '/ajax/ajax_sendRequest.php',
        data: data,
        success: function(json){
            var status = json.status;
            var description = json.description;

            if ( status === 0 ){
                resMsg("ok", description);
                doAction('accsearch');
            } else if ( status === 10){
                resMsg("error", description);
                doLogout();
            } else {
                resMsg("error", description);
            }
        },
        error: function(jqXHR, textStatus, errorThrown){ 
            resMsg("error", 'Oops...' + LANG[0]);
        },
        complete: function(){$.fancybox.hideLoading();}
    });
}

// Función para guardar la configuración
function configMgmt(action){
    var data, url, txt, activeTab;
    
    
    switch(action){
        case "addcat":
            frm = 'frmAddCategory';
            url = APP_ROOT + '/ajax/ajax_categorymgmt.php';
            break;
        case "editcat":
            frm = 'frmEditCategory';
            url = APP_ROOT + '/ajax/ajax_categorymgmt.php';
            break;
        case "delcat":
            frm = 'frmDelCategory';
            url = APP_ROOT + '/ajax/ajax_categorymgmt.php';
            break;
        case "saveconfig":
            $("#allowed_exts option").prop('selected',true);
            $("#wikifilter option").prop('selected',true);
            $("#ldapuserattr option").prop('selected',true);
            
            frm = 'frmConfig';
            url = APP_ROOT + '/ajax/ajax_configsave.php';
            break;
        case "savempwd":
            frm = 'frmCrypt';
            url = APP_ROOT + '/ajax/ajax_configsave.php';
            break;
        case "backup":
            frm = 'frmBackup';
            url =  APP_ROOT + '/ajax/ajax_backup.php';
            break;
        case "migrate":
            frm = 'frmMigrate';
            url =  APP_ROOT + '/ajax/ajax_migrate.php';
            break;
        default:
            return;
    }    
    
    data = $('#' + frm).serialize();
    activeTab = $('#' + frm + ' input[name="active"]').val() - 1;
    $.fancybox.showLoading();

    $.ajax({
        type: 'POST',
        dataType: 'xml',
        url: url,
        data: data,
        success: function(xml){
            var status = parseInt($(xml).find("status").text());
            var description = $(xml).find("description").text();

            if ( status === 0 ){
                resMsg("ok", description);
                doAction('configmenu','',activeTab);
            } else if ( status === 10){
                doLogout();
            } else {
                resMsg("error", description);
            }
        },
        error:function(jqXHR, textStatus, errorThrown){
            txt = LANG[1] + '<p>' + errorThrown + textStatus + '</p>';
            resMsg("error", txt);
        },
        complete: function(){$.fancybox.hideLoading();}
    });

    return false;
}

// Función para descargar/ver archivos de una cuenta
function downFile(id, sk, action){
    var data = {'fileId' : id, 'sk' : sk, 'action': action};
    
    if ( action === 'view'){
        $.fancybox.showLoading();
        
	$.ajax({
            type : "POST",
            cache : false,
            url : APP_ROOT + "/ajax/ajax_files.php",
            data : data,
            success: function(response) {
                if ( response ){
                    $.fancybox(response,{padding: [10,10,10,10]});
                    // Actualizar fancybox para adaptarlo al tamaño de la imagen
                    setTimeout(function() {$.fancybox.update();}, 1000);
                } else{
                    resMsg("error", LANG[23]);
                }

            },
            complete: function(){$.fancybox.hideLoading();}
	});
    } else if ( action === 'download') {
        $.fileDownload(APP_ROOT + '/ajax/ajax_files.php',{'httpMethod' : 'POST','data': data,});
    }
}

// Función para obtener la lista de archivos de una cuenta
function getFiles(id, isDel, sk){
    var data = {'id' : id, 'del' : isDel, 'sk' : sk};
		
    $.ajax({
        type : "GET",
        cache : false,
        url : APP_ROOT + "/ajax/ajax_getFiles.php",
        data : data,
        success: function(response) {
            $('#downFiles').html(response);
        },
        complete: function(){$.fancybox.hideLoading();}
    });
}

// Función para eliminar archivos de una cuenta
function delFile(id, sk, accid){
    var atext = '<div id="alert"><p id="alert-text">' + LANG[24] + '</p></div>';
    
    alertify.confirm(atext, function (e) {
        if (e) {
            $.fancybox.showLoading();
            
            var data = {'fileId': id, 'action': 'delete', 'sk' : sk};

            $.post( APP_ROOT + '/ajax/ajax_files.php', data, 
                function( data ) {
                    $.fancybox.hideLoading();
                    resMsg("ok", data);
                    $("#downFiles").load( APP_ROOT + "/ajax/ajax_getFiles.php?id=" + accid +"&del=1&is_ajax=1&sk=" + sk);
                }
            );
        }
    });
}

function dropFile(accountId, sk, maxsize){
    var dropfiles = $('#dropzone');
    var file_exts_ok = dropfiles.attr('data-files-ext').toLowerCase().split(',');
    
    dropfiles.filedrop({
        fallback_id: 'inFile',
        paramname: 'inFile', // $_FILES name
        maxfiles: 5,
        maxfilesize: maxsize, // in mb
        allowedfileextensions: file_exts_ok,
        url: APP_ROOT + '/ajax/ajax_files.php',
        data: {
            sk: sk,
            accountId: accountId,
            action: 'upload',
            is_ajax: 1
        },
        uploadFinished: function(i, file, response) {
            $.fancybox.hideLoading();

            var sk = $('input:[name=sk]').val();
            $("#downFiles").load(APP_ROOT + "/ajax/ajax_getFiles.php?id=" + accountId + "&del=1&is_ajax=1&sk=" + sk);

            resMsg("ok", response);
        },
        error: function(err, file) {
            switch (err) {
                case 'BrowserNotSupported':
                    resMsg("error", LANG[25]);
                    break;
                case 'TooManyFiles':
                    resMsg("error", LANG[26] + ' (max. ' + this.maxfiles + ')');
                    break;
                case 'FileTooLarge':
                    resMsg("error", LANG[27] + ' ' + maxsize + ' MB' + '<br>' + file.name);
                    break;
            case 'FileExtensionNotAllowed':
                    resMsg("error", LANG[28]);
                    break;
                default:
                    break;
            }
        },
        uploadStarted: function(i, file, len) {
            $.fancybox.showLoading();
        },
    });
}


// Función para mostrar los registros de usuarios y grupos
function usersData(id, type, sk, active, view){
    var data = {'id' : id, 'type' : type, 'sk' : sk, 'active' : active, 'view' : view, 'is_ajax' : 1};
    var url = APP_ROOT + '/ajax/ajax_usersMgmt.php';

    $.fancybox.showLoading();

    $.ajax({
        type: 'POST',
        dataType: 'html',
        url: url,
        data: data,
        success: function(response){
            $.fancybox(response,{
                padding: [0,10,10,10],
                afterClose: function(){doAction('usersmenu','',active);}
            });
        },
        error:function(jqXHR, textStatus, errorThrown){
            var txt = LANG[1] + '<p>' + errorThrown + textStatus + '</p>';
            resMsg("error", txt);
        },
        complete: function(){$.fancybox.hideLoading();}
    });
}

// Función para editar los registros de usuarios y grupos
function usersMgmt(frmId, isDel, id, type, sk){
    var data;
    var url = '/ajax/ajax_usersSave.php';
    
    if ( isDel === 1 ){
        var data = {'id' : id, 'type' : type, 'action' : 4, 'sk' : sk };
        var atext = '<div id="alert"><p id="alert-text">' + LANG[21] + '</p></div>';
        var active = frmId;
        
        alertify.confirm(atext, function (e) {
            if (e) {
                usersAjax(data, url);
                doAction('usersmenu','',active)
            }
        });
    } else {
        data = $("#" + frmId).serialize();
        //type = parseInt($('input:[name=type]').val());
        
        usersAjax(data, url);
    } 
}

// Función para realizar la petición ajax de gestión de usuarios
function usersAjax(data, url){
$.fancybox.showLoading();

$.ajax({
    type: 'POST',
    dataType: 'xml',
    url: APP_ROOT + url,
    data: data,
    success: function(xml){
        var status = parseInt($(xml).find("status").text());
        var description = $(xml).find("description").text();
        description = description.replace(/;;/g,"<br />");

        switch(status){
            case 0:
                $.fancybox.close();
                resMsg("ok", description);
                break;
            case 1:
                $.fancybox.close();
                resMsg("error", description);
                break;
            case 2:
                $("#resFancyAccion").html('<span class="altTxtError">' + description + '</span>');
                $("#resFancyAccion").show();
                break;
            case 3:
                $.fancybox.close();
                resMsg("warn", description);
                break;
            case 10:
                doLogout();
                break;
            default:
                return;
         }  
    },
    error:function(jqXHR, textStatus, errorThrown){
        var txt = LANG[1] + '<p>' + errorThrown + textStatus + '</p>';
        resMsg("error", txt);
    },
    complete: function(){$.fancybox.hideLoading();}
});
}

// Función para mostrar el formulario para cambio de clave de usuario
function usrUpdPass(id,usrlogin){  
    var data = {'usrid': id, 'usrlogin': usrlogin, 'is_ajax' : 1};
    
    $.fancybox.showLoading();

    $.ajax({
        type : "GET",
        cache : false,
        url : APP_ROOT + '/ajax/ajax_usrpass.php',
        data : data,
        success: function(data) {
            if ( data.length === 0 ){
                doLogout();
            } else {
               $.fancybox(data,{padding: 0});
            }
        }
    });
}

// Función para verificar si existen actualizaciones
function checkUpds(){
    $.ajax({
        type: 'GET',
        dataType: 'html',
        url: APP_ROOT + '/ajax/ajax_checkupds.php',
        timeout: 5000,
        success: function(response){
            $('#updates').html(response);
        },
        error:function(jqXHR, textStatus, errorThrown){
            $('#updates').html('!');
        }
    });      
}

// Función para añadir opciones a un select desde input
function addSelOption(dst_id,src_id){
    var dup = 0;
    var value_txt = $("#" + src_id).val().toUpperCase();
    
    if ( typeof(value_txt) == "undefined" || value_txt == "" ){
        resMsg("error", LANG[10]);
    } else {
        $("#" + dst_id + " option").each(function(){
            if ( $(this).val().toUpperCase() == value_txt ){
                resMsg("error", LANG[11]);
                dup = 1;
            }
        });

        if ( dup == 0 ){
            $("#" + dst_id).append(new Option(value_txt, value_txt, true, true));
            resMsg("ok", LANG[13]);
        }
        
        $("#" + src_id).val('');
        $("#" + dst_id + "option").prop('selected',true);
    }
}

// Función para eliminar opciones de un select
function delSelOption(id){
    var value = $("#" + id +" option:selected").val();
    
    if ( typeof(value) == "undefined" ){
        resMsg("error", LANG[12]);
    } else {
        $("#" + id +" option:selected").remove();
        resMsg("ok", LANG[14]);
    }
}

function showOptional(me){
    $(me).hide();
    $(me).parent().css('width','15em');
    var actions =  $(me).closest('.cell-actions').children('.actions-optional');
    actions.show(250);
}

// Función para obtener el tiempo actual en milisegundos
function getTime(){
    t = new Date();
    return t.getTime();
}

// Función para generar claves aleatorias. 
// By Uzbekjon from  http://jquery-howto.blogspot.com.es
function password(length, special, fancy, dstId) {
    var iteration = 0;
    var password = "";
    var randomNumber;
    
    if(special == undefined){
        var special = false;
    }
    
    while(iteration < length){
        randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
        if( ! special ){
            if ((randomNumber >=33) && (randomNumber <=47)) { continue; }
            if ((randomNumber >=58) && (randomNumber <=64)) { continue; }
            if ((randomNumber >=91) && (randomNumber <=96)) { continue; }
            if ((randomNumber >=123) && (randomNumber <=126)) { continue; }
        }
        iteration++;
        password += String.fromCharCode(randomNumber);
    }
    
    if ( fancy == true ){
        $("#viewPass").attr("title",password);
        //alertify.alert('<div id="alert"><p id="alert-text">' + LANG[15] + '</p><p id="alert-pass"> ' + password + '</p>');
    } else {
        alertify.alert('<div id="alert"><p id="alert-text">' + LANG[15] + '</p><p id="alert-pass"> ' + password + '</p>');
    }
   
   if ( dstId ){
        checkPassLevel(password);
        $('#' + dstId +'  input:password').val(password);
        $('#' + dstId + ' #passLevel').show(500);
   } else{
        checkPassLevel(password);
        $('input:password').val(password);
        $('#passLevel').show(500);
   }
    //return password;
}

// Funciónes para analizar al fortaleza de una clave
// From http://net.tutsplus.com/tutorials/javascript-ajax/build-a-simple-password-strength-checker/
function checkPassLevel(password, dstId){
    strPassword= password;
    charPassword = strPassword.split("");

    num.Excess = 0;
    num.Upper = 0;
    num.Numbers = 0;
    num.Symbols = 0;
    bonus.Combo = 0; 
    bonus.FlatLower = 0;
    bonus.FlatNumber = 0;
    baseScore = 0;
    score = 0;

    if (charPassword.length >= minPasswordLength){
        baseScore = 50;	
        analyzeString();	
        calcComplexity();		
    } else {
        baseScore = 0;
    }

    if ( dstId ){
        outputResult(dstId);
    } else{
        outputResult(dstId);
    }
}

function analyzeString (){	
    for (i=0; i<charPassword.length;i++){
        if (charPassword[i].match(/[A-Z]/g)) {num.Upper++;}
        if (charPassword[i].match(/[0-9]/g)) {num.Numbers++;}
        if (charPassword[i].match(/(.*[!,@,#,$,%,^,&,*,?,_,~])/)) {num.Symbols++;} 
    }

    num.Excess = charPassword.length - minPasswordLength;

    if (num.Upper && num.Numbers && num.Symbols){
        bonus.Combo = 25; 
    }

    else if ((num.Upper && num.Numbers) || (num.Upper && num.Symbols) || (num.Numbers && num.Symbols)){
        bonus.Combo = 15; 
    }

    if (strPassword.match(/^[\sa-z]+$/)){ 
        bonus.FlatLower = -15;
    }

    if (strPassword.match(/^[\s0-9]+$/)){ 
        bonus.FlatNumber = -35;
    }
}

function calcComplexity(){
    score = baseScore + (num.Excess*bonus.Excess) + (num.Upper*bonus.Upper) + (num.Numbers*bonus.Numbers) + (num.Symbols*bonus.Symbols) + bonus.Combo + bonus.FlatLower + bonus.FlatNumber;
}	

function outputResult(dstId){
    var complexity;
    
    if ( dstId ){
        complexity = $('#' + dstId + ' #passLevel');
    } else {
        complexity = $('#passLevel');
    }

    if ( charPassword.length == 0 ){
        complexity.empty().removeClass("weak good strong strongest");
    } else if (charPassword.length < minPasswordLength){
        complexity.html(LANG[20]).removeClass("good strong strongest").addClass("weak");
    } else if (score<50){
        complexity.html(LANG[18]).removeClass("good strong strongest").addClass("weak");
    } else if (score>=50 && score<75){
        complexity.html(LANG[17]).removeClass("weak strong strongest").addClass("good");
    } else if (score>=75 && score<100){
        complexity.html(LANG[16]).removeClass("weak good strongest").addClass("strong");
    } else if (score>=100){
        complexity.html(LANG[19]).removeClass("weak good strong").addClass("strongest");
    }
}

// Función para mostrar mensaje con Fancybox
function resMsg(type, txt, url, action){
    if ( typeof(url) !== "undefined" ){
        $.ajax({ url: url, type: 'get', dataType: 'html', async: false, success: function(data) { txt = data; }});
    }
    
    var html;
    
    switch(type){
        case "ok":
            //html = '<div id="fancyMsg" class="msgOk">' + txt + '</div>';
            return alertify.success(txt);
        case "error":
            //html = '<div id="fancyMsg" class="msgError">' + txt + '</div>';
            return alertify.error(txt);
        case "warn":
            //html = '<div id="fancyMsg" class="msgWarn">' + txt + '</div>';
            return alertify.log(txt);
        case "info":
            html = '<div id="fancyMsg" class="msgInfo">' + txt + '</div>';
            break;
        case "none":
            html = txt;
            break;
        case "nofancyerror":
            html = '<P CLASS="error round">Oops...<BR />' + LANG[1] + '<BR />' + txt + '</P>';
            return html;
            break;
        default:
            //html = '<div id="fancyMsg" class="msgError">Oops...<br /' + LANG[1] + '</div>';
            return alertify.error(txt);
    }
        
    $.fancybox(html,{afterLoad: function(){
        $('.fancybox-skin,.fancybox-outer,.fancybox-inner').css({'border-radius':'25px','-moz-border-radius':'25px','-webkit-border-radius':'25px'});
        },afterClose : function() { if ( typeof(action) !== "undefined" ) eval(action);} });
}

// Combo
(function( $ ) {
    $.widget( "custom.combobox", {
        _create: function() {
            this.wrapper = $("<span>").addClass( "custom-combobox" ).insertAfter( this.element );
            this.element.hide();
            this._createAutocomplete();
            this._createShowAllButton();
        },
        _createAutocomplete: function() {
            var selected = this.element.children( ":selected" ),
            value = selected.val() ? selected.text() : "";
            this.input = $( "<input>" )
            .appendTo( this.wrapper )
            .val( value )
            .attr( { 'title' : "", 'placeholder' : this.options.placeholder })
            .addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
            .autocomplete({
                delay: 0,
                minLength: 0,
                source: $.proxy( this, "_source" )
            })
            .tooltip({
                tooltipClass: "ui-state-highlight"
            })
    
            this._on( this.input, {
                autocompleteselect: function( event, ui ) {
                    ui.item.option.selected = true;
                    this._trigger( "select", event, { item: ui.item.option });
                    if ( this.options.dosearch === 1 ){
                        accSearch(0); 
                    }                    
                },
                autocompletechange: "_removeIfInvalid"
            });
        },
        _createShowAllButton: function() {
            var input = this.input,
            wasOpen = false;
            $( "<a>" )
            .attr( "tabIndex", -1 )
            //.attr( "title", "Show All Items" )
            .tooltip()
            .appendTo( this.wrapper )
            .button({
                icons: { primary: "ui-icon-triangle-1-s"},
            text: false
            })
            .removeClass( "ui-corner-all" )
            .addClass( "custom-combobox-toggle ui-corner-right" )
            .mousedown(function() {
                wasOpen = input.autocomplete( "widget" ).is( ":visible" );
            })
            .click(function() {
                input.focus();
                // Close if already visible
                if ( wasOpen ) {
                    return;
                }
                // Pass empty string as value to search for, displaying all results
                input.autocomplete( "search", "" );
            });
        },
        _source: function( request, response ) {
            var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
            response( this.element.children( "option" ).map(function() {
                var text = $( this ).text();
                if ( this.value && ( !request.term || matcher.test(text) ) )
                    return { label: text, value: text, option: this };
            }));
        },
        _removeIfInvalid: function( event, ui ) {
            // Selected an item, nothing to do
            if ( ui.item ) {
                return;
            }
            // Search for a match (case-insensitive)
            var value = this.input.val(),
            valueLowerCase = value.toLowerCase(),
            valid = false;
            this.element.children( "option" ).each(function() {
                if ( $( this ).text().toLowerCase() === valueLowerCase ) {
                    this.selected = valid = true;
                    return false;
                }
            });
            // Found a match, nothing to do
            if ( valid ) {
                return;
            }
            // Remove invalid value
            this.input
            .val( "" )
            //.attr( "title", value + " didn't match any item" )
            .tooltip( "open" );
            this.element.val( "" );
            this._delay(function() {
                this.input.tooltip( "close" ).attr( "title", "" );
            }, 2500 );
            this.input.data( "ui-autocomplete" ).term = "";
        },
        _destroy: function() {
            this.wrapper.remove();
            this.element.show();
        }
    });
})( jQuery );