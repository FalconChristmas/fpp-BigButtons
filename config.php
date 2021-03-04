<?
require 'bb-common.php';
?>

<div id="global" class="settings">
<link  rel="stylesheet" href="/jquery/colpick/css/colpick.css"/>
<script src="/jquery/colpick/js/colpick.js"></script>
<script>
var bbButtonGap=15;
var bb={};
var bigButtonsConfig=null;
var legacyColorNames={"aqua":'00FFFF',
"blue":'0000FF',
"chocolate":'D2691E',
"coral":'FF7F50',
"cyan":'00FFFF',
"darkcyan":'008B8B',
"green":'008000',
"grey":'808080',
"ivory":'FFFFF0',
"lightblue":'ADD8E6',
"lightcoral":'F08080',
"lightcyan":'E0FFFF',
"lightgrey":'D3D3D3',
"lightgreen":'90EE90',
"lightpink":'FFB6C1',
"lightyellow":'FFFFE0',
"olive":'808000',
"orange":'FFA500',
"pink":'FFC0CB',
"plum":'DDA0DD',
"purple":'800080',
"red":'FF0000',
"slategrey":'708090',
"tan":'D2B48C',
"white":'FFF5EE',
"whitesmoke":'F5F5F5',
"yellow":'FFFF00'}


function SaveBigButtonConfig(config) {
    var data = JSON.stringify(config);
    $.ajax({
        type: "POST",
        url: 'fppjson.php?command=setPluginJSON&plugin=fpp-BigButtons',
        dataType: 'json',
        async: false,
        data: data,
        processData: false,
        contentType: 'application/json',
        success: function (data) {
           $('#saveBigButtonConfigButton').addClass('success');
           setTimeout(function(){$('#saveBigButtonConfigButton').removeClass('success')},3000);
        }
    });
}

function GetButton(i,tab_i,v) {
    
    var button = {
        "description": $('#button_'+tab_i+'-'+i+'_Title').val(),
        "color": $('#button_'+tab_i+'-'+i+'_color').val(),
        "buttonWidthRatio":$(v).data('button-width-ratio'),
        "buttonHeightValue":$(v).data('button-height-value'),
    };
    CommandToJSON('button_'+tab_i+'-'+i+'_Command', 'tableButton'+tab_i+'-'+i, button);
    return button;
}
function SaveButtons() {
    

    $.each($('.buttonList'),function(tab_i,tab_v){
        bigButtonsConfig[tab_i]={
            title: $('.buttonTabs .buttonPageTitleValue').eq(tab_i).html(),
            buttons:[],
            fontSize: $('#buttonFontSize').val()
        };
        $.each($(tab_v).children(),function(i,v){
            var key = ""+i;
            var button = GetButton(i,tab_i,v);
            bigButtonsConfig[tab_i]["buttons"][key] = button;

        });
    }); 
    SaveBigButtonConfig(bigButtonsConfig);
}

function updateButtonRow(i,v,tab_i){
    var $newButtonRow = $(v);
    var newButtonRowColor = 'button_'+tab_i+'-'+i+'_color';
    var newButtonRowCommand = 'button_'+tab_i+'-'+i+'_Command';
    var newButtonRowTitle = 'button_'+tab_i+'-'+i+'_Title';
    var newButtonRowTable = 'tableButton'+tab_i+'-'+i;
    $newButtonRow.data('bbKey',i);
    $newButtonRow.find('.buttonCommand').attr('id',newButtonRowCommand);
    $newButtonRow.find('.buttonTitle').attr('id',newButtonRowTitle);
    $newButtonRow.find('.buttonColor').attr('id',newButtonRowColor);
    $newButtonRow.find('[id^="tableButton"]').each(function(){
        var oldId = $(this).prop('id')
        var idArr = oldId.split('_');
        idArr[0]=newButtonRowTable
        $(this).attr('id',idArr.join('_'))

    });
    return $newButtonRow;
}
function updateButtonLists() {
    $.each($('.buttonList'), function() {
        $.each($(this).children(), function(iteration, value) {
            $(this).removeClass('bb_newButton');
            updateButtonRow(iteration,value,$(this).parent().data('tab-id'));
        });           
    })
}
function setButtonCommandSummaryTitle($row,value){
    if(!value){
        value='Select a Command'
    }
    $row.find('.bb_commandSummaryTitle').html(value);
}
function setButtonWidthRatio($row,ratio){
    ratio=Math.min(1,ratio)
    $row.data('button-width-ratio',ratio).removeClass (function (index, className) {
        return (className.match (/(^|\s)bbw-\S+/g) || []).join(' ');
    }).addClass('bbw-'+Math.round(ratio*100));
}
function setButtonHeightValue($row,value){
    $row.data('button-height-value',value).removeClass (function (index, className) {
        return (className.match (/(^|\s)bbh-\S+/g) || []).join(' ');
    }).addClass('bbh-'+Math.round(value));
}
function setRowColor($row,hex){
    $row.css({'background-color': '#'+hex}).data('row-color','#'+hex);
    $row.find('.buttonColor').css({'background-color': '#'+hex}).colpickHide().val('#'+hex);
}
function launchButtonConfigModal($buttonRow){
    $buttonRow.find('.buttonCommandWrap').fppDialog({
        title: 'Command for '+($buttonRow.find('.buttonTitle').val()==''?'New Button':$buttonRow.find('.buttonTitle').val()),
        buttons:{
            Done:{
                click:function(){
                    $buttonRow.find('.buttonCommandWrap').fppDialog('close');
                },
                class:'btn-success'
            }
        }
    })
}
function createButtonRow(i,v,tab_i){
    var $newButtonRow = $($(".configRowTemplate").html());
    var newButtonRowColor = 'button_'+tab_i+'-'+i+'_color';
    var newButtonRowCommand = 'button_'+tab_i+'-'+i+'_Command';
    var newButtonRowTitle = 'button_'+tab_i+'-'+i+'_Title';
    var newButtonRowTable = 'tableButton'+tab_i+'-'+i;
    $newButtonRow.data('bbKey',i);
    $newButtonRow.find('.buttonCommand').attr('id',newButtonRowCommand).on('change',function(){
        CommandSelectChanged(newButtonRowCommand, newButtonRowTable, true);
        setButtonCommandSummaryTitle($newButtonRow,$(this).val());
    })
    
    $newButtonRow.find('.buttonTitle').attr('id',newButtonRowTitle).css({
        fontSize:bigButtonsConfig[0].fontSize
    });
    
    $newButtonRow.find('.bb_commandSummary').click(function(){
        launchButtonConfigModal($newButtonRow);
    });
    $newButtonRow.find('.buttonColor').attr('id',newButtonRowColor);
    $newButtonRow.find('.tableButton').attr('id',newButtonRowTable);

    $newButtonRow.find('.buttonDelete').click(function(){
        $(this).closest('.bb_configRow').remove();
        $.each($('.buttonList'), function(tab_iteration, tab_value) {
            $.each($(this).find('li'), function(iteration, value) {
                $(this).removeClass('bb_newButton');
                updateButtonRow(iteration,value,tab_iteration);
            });           
        })
    });

    $('.buttonLists').children().eq(tab_i).append($newButtonRow);
    LoadCommandList('button_'+tab_i+'-'+i+'_Command');
    var buttonWidthRatio = 0.5;
    var buttonHeightValue = 13;
    

    var hex = "ff8800";
    if(v){
        if(v.buttonWidthRatio){
            buttonWidthRatio = v.buttonWidthRatio
        }
        if(v.buttonHeightValue){
            buttonHeightValue = v.buttonHeightValue
        }
        hex=v.color;
    }
    setButtonWidthRatio($newButtonRow,buttonWidthRatio);
    setButtonHeightValue($newButtonRow,buttonHeightValue);
    var buttonsOnSameRow =[];
    var heightBeforeResize;
    $newButtonRow.resizable({
      grid: [bb.pageContentWidth/48,1],
      start:function(event,ui){
        if(!$(event.originalEvent.target).hasClass('ui-resizable-e')){ //dont touch the height if we are resizing width
            heightBeforeResize = $(this).height();
            var originY = $newButtonRow.position().top;
            $(this).removeClass (function (index, className) {
                return (className.match (/(^|\s)bbh-\S+/g) || []).join(' ');
            })
            buttonsOnSameRow =[];
            $(this).siblings().each(function(){
                if(originY == $(this).position().top ){
                    buttonsOnSameRow.push($(this));
                    $(this).removeClass (function (index, className) {
                        return (className.match (/(^|\s)bbh-\S+/g) || []).join(' ');
                    })
                }
            })
        }

      },
      stop: function( event, ui ) {
        setButtonWidthRatio( $newButtonRow,1/(bb.pageContentWidth/$newButtonRow.width()));
        if(!$(event.originalEvent.target).hasClass('ui-resizable-e')){ //dont touch the height if we are resizing width
            $.each(buttonsOnSameRow, function(i,$sameRowButton){
                setButtonHeightValue( $sameRowButton,$newButtonRow.height()/10);
            });
            setButtonHeightValue( $newButtonRow,$newButtonRow.height()/10);
            $newButtonRow.height('');
        }
        $newButtonRow.width('');
      }
    });

    $newButtonRow.find('.buttonColor').colpick({
        colorScheme:'flat',
        layout:'rgbhex',
        color:hex,
        onSubmit:function(hsb,newHex,rgb,el) {
            setRowColor($newButtonRow,newHex);
        }
    });
    setRowColor($newButtonRow,hex);

    return $newButtonRow;
}
function bbHandleWindowResize(){
    bb.pageContentWidth = $('.buttonLists').width()-bbButtonGap;

}
$( function() {

    $(window).resize(bbHandleWindowResize);
    bbHandleWindowResize();

    $('#saveBigButtonConfigButton').click(function(){
        SaveButtons();
    });
 
    $('#buttonTitle').on('change keydown paste input', function() {
        bigButtonsConfig[0]['title'] = $(this).val();
    });

    $.ajax({
        type: "GET",
        url: 'fppjson.php?command=getPluginJSON&plugin=fpp-BigButtons',
        //url: 'legacyBigButtonsSampleConfig.json',
        dataType: 'json',
        contentType: 'application/json',
        success: function (data) {

            if(typeof data==="string"){
                bigButtonsConfig = $.parseJSON(data);
            }else{
                bigButtonsConfig = data;
            }
            if(!Array.isArray(bigButtonsConfig)){
                // if the json is a flat array, it is a legacy config
                // so we need to upgrade to support multiple tabs
                $.each(bigButtonsConfig.buttons,function(i,v){
             
                    bigButtonsConfig.buttons[i].color=legacyColorNames[v.color]
                })
                bigButtonsConfig = [bigButtonsConfig];
            }

            if(bigButtonsConfig.length<1){
                bigButtonsConfig.push([{ "title": "", "fontSize": 12, "buttons": { "1": {}}}])
            }
            
            $.each(bigButtonsConfig,function(tab_i,tab_v){
                var tab = createTab(tab_v.title,tab_i);
                $.each(bigButtonsConfig[tab_i].buttons,function(i,v){                   
                    $newButtonRow=createButtonRow(i,v,tab_i);
                    $newButtonRow.find('.buttonTitle').val(v.description);
                    $newButtonRow.find('.buttonColor').val(v.color);
                    PopulateExistingCommand(v, 'button_'+tab_i+'-'+i+'_Command',  'tableButton'+tab_i+'-'+i, true);
                    setButtonCommandSummaryTitle($newButtonRow,$('#button_'+tab_i+'-'+i+'_Command').val());
    
                })
                $('#buttonFontSize').val(bigButtonsConfig[tab_i].fontSize).on('input change',function(){
                    $('.bb_fontSizeDisplay').html($(this).val());
                    bigButtonsConfig[tab_i]['fontSize']=$(this).val();
                    $('.buttonTitle').css({
                        fontSize:parseInt($('#buttonFontSize').val())
                    });
                });
                $('.bb_fontSizeDisplay').html(bigButtonsConfig[tab_i].fontSize);           
            });
            $( ".buttonList" ).disableSelection();
            $('.buttonTabs').children().eq(0).addClass('bb-active');
            $('.buttonLists').children().eq(0).addClass('bb-active');
        }
    });



    function createTab(title,tab_i){
        var $buttonTab = $($('.buttonTabTemplate').html());
        $buttonTab.find('.buttonPageTitleValue').html(title);
        $buttonTab.attr('data-tab-id',tab_i);
        var $newButtonList = $('<ul class="buttonList"></ul>').attr('data-tab-id',tab_i);
        $buttonTab.find('.buttonPageTitleValue').click(function(){
            $buttonTab.addClass('bb-active').siblings().removeClass('bb-active');
            $newButtonList.addClass('bb-active').siblings().removeClass('bb-active');
        });
        $buttonTab.find('.toggleButtonPageTitle').click(function(){
            if($buttonTab.find('.buttonPageTitleValue').is("[contenteditable]")){
                $buttonTab.removeClass('editable');
                $buttonTab.find('.buttonPageTitleValue').removeAttr('contenteditable');
            }else{
                $buttonTab.addClass('editable');
                $buttonTab.find('.buttonPageTitleValue').attr('contenteditable','').focus();
            }
        });

        $newButtonList.sortable({
            handle: ".bb_configRowHandle",
        
            update:function(){
                updateButtonLists();
            }
        });
        $buttonTab.droppable({
            tolerance:"pointer",
            hoverClass:'droppable-hovered',
            drop:function(event,ui){
                dropped = true;
                //$(ui.draggable).css('border','1px solid red');
                
                var $targetButtonList=$('.buttonList[data-tab-id='+$(event.target).data('tab-id')+']');
                var targetTabId = $(event.target).data('tab-id');
                var sourceTabId = $(ui.draggable).parent().data('tab-id');
                var bbKey = ui.draggable.data('bbKey');

                var v = GetButton(bbKey,sourceTabId,ui.draggable);
                var i = $targetButtonList.length+1;
                $newButtonRow=createButtonRow(i,v,targetTabId);
                $newButtonRow.find('.buttonTitle').val(v.description);
                $newButtonRow.find('.buttonColor').val(v.color);
                PopulateExistingCommand(v, 'button_'+targetTabId+'-'+i+'_Command', 'tableButton'+targetTabId+'-'+i, true);
                
                ui.draggable.remove();
                updateButtonLists();
                //$(event.target).addClass('droppable-dropped');
            }
        });
        $('.buttonTabs').append($buttonTab);
        $('.buttonLists').append($newButtonList );
        return {$tab:$buttonTab,$list:$newButtonList};
    }
    $("#bb_addNewButton").click(function(){
        var i=$( ".bb-active.buttonList" ).children().length;
        var tab_i = $( ".bb-active.buttonList" ).data('tab-id');
        var $newButtonRow = createButtonRow(i,null,tab_i);
        $newButtonRow.addClass('bb_newButton').one('animationend',function(){
            $newButtonRow.removeClass('bb_newButton');
            launchButtonConfigModal($newButtonRow);
        });
        
    });
    $("#bb_addNewTab").click(function(){
        var tab = createTab('New Tab',$( ".buttonTabs" ).children().length);     
    });
});

</script>
<template class="buttonTabTemplate">
    <li class="buttonTab">
        <span class="buttonPageTitleValue"></span>
        <span  class="toggleButtonPageTitleWrap">
            <button class="bb_circleButton toggleButtonPageTitle"><i class="fpp-icon-edit"></i><i class="fpp-icon-check"></i></button>   
        </span>
         
    </li>
</template>
<template class="configRowTemplate">
    <li class="ui-state-default bb_configRow">
        <div class="bb_configRowHandle">
            <div class="rowGrip">
                  <i class="rowGripIcon fpp-icon-grip"></i>
            </div>
        </div>
        
        <div class="bb_configRowBody">
            <div class="bb_buttonTitleWrap">
                <input type='text' class="buttonTitle" placeholder="Name Your Button" id='button_TPL_Title' maxlength='80'  value='<?=$description;?>'></input>
            </div>
            <div class="bb_commandSummary">
                <i class="fas fa-fw fa-terminal fa-nbsp"></i><strong class="bb_commandSummaryTitle"></strong><button class="buttons btn-outline-light bb_commandEditButton"><i class="fas fa-cog"></i></button>
            </div>
        </div>

        <div class="buttonCommandWrap">
            <select id='button_TPL_Command' class="buttonCommand"><option value="" disabled selected>Select a Command</option></select>
            <div class="bb_commandTableWrap">
                <div class="bb_commandTableCrop">
                <table border=0 id='tableButtonTPL' class="tableButton">

                </table>            
                </div>
            </div>
        </div>


        <div class="bb_buttonActions">
            <button id='button_TPL_color' class="bb_circleButton buttonColor" type="button"><i class="fas fa-paint-brush"></i></button>
            <button class="bb_circleButton buttonDelete">Delete</button>
        </div>
        
    </li>
</template>

<div class="row">
    <div>
        <div class="labelHeading">Text Font Size </div>
        <div class="bb_fontSizeControls">
            <span class='bb_fontSizeDisplay'></span>
            <div class="bb_fontSizeControlsInputCol"><input  type="range" min=10 max=64 id='buttonFontSize'></div>
        </div>
    </div>
    <div class="bb_actionButtons">
        <button id="bb_addNewButton" class="buttons">Add a New Button</button>
        <input type="button" value="Save Buttons" class="buttons" id="saveBigButtonConfigButton">

    </div>

</div>

<div class="buttonTabWrapper">
    <ul class="buttonTabs">
    
    </ul>
    <div>
        <button id="bb_addNewTab"><i class="fas fa-plus"></i></button>
    </div>
    
</div>
<div class="buttonLists">

</div>


<style type="text/css">

*, *:before, *:after {
  box-sizing: border-box;
}
.buttonList li,.buttonList{
    margin:0;
    list-style-type:none;
    padding:0;
    box-sizing: border-box;
}
.buttonList{    
    flex-wrap:wrap;
    display:none;
    margin-left:-0.5%;
    margin-right:-0.5%;
}
.buttonList.bb-active{
    display:flex;
}
.buttonList:after {
  content: "";
  display: table;
  clear: both;
}
.buttonList li{
    border-radius:12px;
    transition: 0.2s all cubic-bezier(.01,.79,.32,.99);
    position:relative;
    border:none;
    margin:0.5%;
}
.buttonList li.ui-resizable-resizing{
    transition: 0.2s width cubic-bezier(.01,.79,.32,.99);
}
.buttonList li:hover {
    box-shadow: 0px 8px 15px 3px rgba(0,0,0,0.15);
  
}
.buttonList li.bb_newButton{
    -webkit-animation: scale-up-center 0.4s cubic-bezier(.01,.79,.32,.99) both;
	        animation: scale-up-center 0.4s cubic-bezier(.01,.79,.32,.99) both;
}
.buttonList li.ui-sortable-helper {
    transform:scale(1.05);
    opacity:0.8;
    box-shadow: 10px 10px 30px 5px rgba(0,0,0,0.1);
    transition: 0.2s transform cubic-bezier(.01,.79,.32,.99),0.2s box-shadow cubic-bezier(.01,.79,.32,.99);
}
.buttonList li td{
    padding-top:0.2em;
    padding-bottom:0.2em;
}
.bb_actionButtons{
    text-align:right;
    flex:1;
    padding-bottom:1.2em;
}
.bb_actionButtons .buttons{
    margin-left:0.5em;
}
.bb_configRowBody{
    height:100%;
    display:flex;
    align-items:center;
    width:100%;
    justify-content:center;
    flex-direction:column;
}
.bb_buttonTitleWrap{
    text-align:center;
}
.bb_commandTableWrap{
    min-height:50px;
    position:relative;
}


#buttonTitle{
    border:0px;
    border-bottom: 1px solid #D2D2D2;
    border-radius:0px;
    padding-left:0px;
    font-size:1.8em;
    width:100%;
    text-align:center;
    font-weight:bold;
    margin-bottom:0.5em;
}
.buttonList li:hover input.buttonTitle {
    border-bottom: 1px solid #fff;
}
.buttonList li input.buttonTitle:focus, .buttonTitle:focus {
  outline-style: none;
  border-bottom: 1px solid #fff;
  box-shadow: 0px 1px 0px 0px #fff;
}
.buttonList li:hover  .buttonTitle:focus {
    border-bottom: 1px solid #fff;
    box-shadow: 0px 1px 0px 0px #fff;
}
.buttonList li input.buttonTitle::-webkit-input-placeholder { 
    color: rgba(255,255,255,0.4);
}
.buttonList li input.buttonTitle::placeholder {
    color: rgba(255,255,255,0.4);
}
.buttonList li input.buttonTitle:placeholder-shown {
    border-bottom: 1px solid rgba(255,255,255,1);
}
#saveBigButtonConfigButton{
    background-color:#2E4260;
    color:#fff;
    background-image: url("data:image/svg+xml,%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='12.3px' height='9.1px' viewBox='0 0 12.3 9.1' style='enable-background:new 0 0 12.3 9.1;' xml:space='preserve'%3E%3Cstyle type='text/css'%3E .st0%7Bfill:%23FFFFFF;%7D%0A%3C/style%3E%3Cdefs%3E%3C/defs%3E%3Cpath class='st0' d='M3.5,8.8L0.3,5.7c-0.5-0.5-0.5-1.2,0-1.7l0,0C0.8,3.5,1.6,3.5,2,4l3.1,3.1c0.5,0.5,0.5,1.2,0,1.7l0,0 C4.7,9.3,4,9.3,3.5,8.8z'/%3E%3Cpath class='st0' d='M5.2,8.8L11.9,2c0.5-0.5,0.5-1.2,0-1.7l0,0c-0.5-0.5-1.2-0.5-1.7,0L3.5,7.1C3,7.6,3,8.3,3.5,8.8l0,0 C4,9.3,4.7,9.3,5.2,8.8z'/%3E%3C/svg%3E%0A");
    background-position: right 20px top 39px;
	background-size: 13px;
    background-repeat:no-repeat;
    transition: 0.2s all cubic-bezier(.01,.79,.32,.99);
    width: 150px;
    padding-right: 16px;
}
#saveBigButtonConfigButton.success{
    animation:success-animation 3s linear both;
    border-color:#56B760;  

}
.bb_pageSettingsTitleCol{
    flex:1;
}
.bb_fontSizeControls{
    display:flex;
}
.bb_fontSizeControlsInputCol{
    margin-left: 1em;
	margin-top: 0.5em;
}
#bb_addNewButton{
    background-position: right 20px center;
	background-size: 10px;
    background-repeat:no-repeat;
    background-image: url("data:image/svg+xml,%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 16.2 16.2' style='enable-background:new 0 0 16.2 16.2;' xml:space='preserve'%3E%3Cstyle type='text/css'%3E .st0%7Bfill:%23171720;%7D%0A%3C/style%3E%3Cpath class='st0' d='M8.1,16.2L8.1,16.2c-0.6,0-1.1-0.5-1.1-1.1V1.1C7,0.5,7.5,0,8.1,0h0c0.6,0,1.1,0.5,1.1,1.1v13.9 C9.2,15.7,8.7,16.2,8.1,16.2z'/%3E%3Cpath class='st0' d='M0,8.1L0,8.1C0,7.5,0.5,7,1.1,7h13.9c0.6,0,1.1,0.5,1.1,1.1v0c0,0.6-0.5,1.1-1.1,1.1H1.1C0.5,9.2,0,8.7,0,8.1z' /%3E%3C/svg%3E%0A");   
    padding-right:40px ;
}
.bb_fontSizeDisplay{
    display: block;
	font-size: 1.3em;
	font-weight: bold;
}
.buttonList li input.buttonTitle{
    text-align:center;
    background-color:transparent;
    border-radius:0;
    border:0;
    border-bottom:1px solid rgba(0,0,0,0);
    color:#fff;
    max-width:87%;
    font-weight:bold;
    margin-bottom:0.2em;
}

.bb_configRowHandle{
    display:block;
    position:absolute;
    top:12px;
    left:12px;
    font-size:1.5em;
    cursor:grab;
    color:#fff;
}
.bb_configRowHandle .rowGripIcon{
    color:#fff;
    opacity:0.7;
}
.bb_configRowHandle .rowGripIcon:hover{
    opacity:1;
}
.bb_commandSummary{
    color:#fff;
    cursor:pointer;
}
.bb_commandSummary button{
    margin-left:0.5em;
}
.buttonCommandWrap{
    text-align:center;
    margin-top:0.5em;
    display:none;
}

.buttonList li  .buttonCommand option{
    color:#000;
}

.buttonList li td{
  
    vertical-align:middle;
}
.buttonList li td * {

    vertical-align:middle;
}
.buttonList li .tableButton {
 
 max-width:100%;
 padding-bottom:1.7em;
}

.buttonList li .tableButton select{
    padding-right:3em;
}
.bb_buttonActions{
    top:1em;
    right:1em;
    position:absolute;
    display:flex;

}
.bb_circleButton{
    display:block;
    appearance:none;
	width:30px;
	height:30px;
    border:0;
    border-radius:50%;
    color:#fff;
    cursor:pointer;
    font-weight:900;
}
.buttonColor {
	border: 1px solid white;
}
.buttonDelete{
    background-color:#F63939;
    background-image: url("data:image/svg+xml,%3C!-- Generator: Adobe Illustrator 24.1.1, SVG Export Plug-In --%3E%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='14px' height='18.3px' viewBox='0 0 14 18.3' style='enable-background:new 0 0 14 18.3;' xml:space='preserve'%3E%3Cstyle type='text/css'%3E .st0%7Bfill:%23FFFFFF;%7D%0A%3C/style%3E%3Cdefs%3E%3C/defs%3E%3Cg%3E%3Cpath class='st0' d='M1,16.3c0,1.1,0.9,2,2,2h8c1.1,0,2-0.9,2-2v-12H1V16.3z M9.9,7.9c0-0.3,0.3-0.6,0.6-0.6c0.3,0,0.6,0.3,0.6,0.6 v6.5c0,0.3-0.3,0.6-0.6,0.6c-0.3,0-0.6-0.3-0.6-0.6V7.9z M6.5,7.9c0-0.3,0.3-0.6,0.6-0.6c0.3,0,0.6,0.3,0.6,0.6v6.5 c0,0.3-0.3,0.6-0.6,0.6c-0.3,0-0.6-0.3-0.6-0.6V7.9z M3.3,7.9c0-0.3,0.3-0.6,0.6-0.6s0.6,0.3,0.6,0.6v6.5c0,0.3-0.3,0.6-0.6,0.6 s-0.6-0.3-0.6-0.6V7.9z'/%3E%3Cpolygon class='st0' points='10.5,1 9.5,0 4.5,0 3.5,1 0,1 0,3 14,3 14,1 '/%3E%3C/g%3E%3C/svg%3E%0A");    background-position:center;
    background-repeat:no-repeat;
    color:rgba(0,0,0,0);
}
.buttonList li .bb_buttonActions button{
    opacity:0.0;
    transition: 0.2s all cubic-bezier(.01,.79,.32,.99);  
    transform:scale(0.5);
    margin-left:0.3em;

}
.buttonList li:hover .bb_buttonActions button{

    cursor:pointer;
    opacity:1;
    transform:scale(1);
}

.scale-up-center {
	-webkit-animation: scale-up-center 0.4s cubic-bezier(0.390, 0.575, 0.565, 1.000) both;
	        animation: scale-up-center 0.4s cubic-bezier(0.390, 0.575, 0.565, 1.000) both;
}
.colpick{
    z-index:4;
}
#bb_addNewTab{
    cursor:pointer;
    appearance:none;
    background-color:rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    color:#F63939;
    border:none;
    padding: 0.9em 1.0em;
    margin-left:0.4em;

}
#bb_addNewTab:hover{
    background-color:#DADADA;
}
.buttonTabWrapper{
    display:flex;
}
.buttonTabs{
    list-style:none;
    padding:0;
    margin:0;
    display:flex;
    padding-bottom:0.5em;
}
.buttonTab {
    list-style:none;
    padding:0em 0.6em;
    margin:0 0.2em;
    position:relative;
    font-weight:bold;
    border-radius:10px;
    border:1px solid #DADADA;
    display:flex;
    align-items:center;
    transition:0.1s cubic-bezier(0.390, 0.575, 0.565, 1.000);
}
.buttonTab:hover,.buttonTab.editable{

}
.buttonTab.bb-active {
    color:#fff;
    background-color:#F63939;
    border-color:#F63939;
}
.buttonTab.ui-droppable-hover {
    transform:scale(1.1);
    border:1px solid rgba(0,0,0,0.15);
    background-color:rgba(0,0,0,0.1);
}
.toggleButtonPageTitleWrap{
    width:0;
    display:block;
    transition:0.1s cubic-bezier(0.390, 0.575, 0.565, 1.000);
}
.toggleButtonPageTitle{
    background-color:#F2BC5F;
    box-shadow: 3px 3px 5px rgb(23 23 32 / 20%);
    transform:scale(0);
    opacity:0;
    transition:0.1s cubic-bezier(0.390, 0.575, 0.565, 1.000);
}
.buttonTab:hover .toggleButtonPageTitle, .buttonTab.editable .toggleButtonPageTitle{
    transform:scale(1);
    opacity:1;   
}
.buttonTab:hover .toggleButtonPageTitleWrap{
    width:30px;
}
.buttonTab.editable .toggleButtonPageTitle{ 
    background-color:#56B760;
}
.toggleButtonPageTitle .fpp-icon-edit{
    display:block;
} 
.toggleButtonPageTitle .fpp-icon-check{
    display:none;
}
.buttonTab.editable .toggleButtonPageTitle .fpp-icon-edit{ 
    display:none;
}
.buttonTab.editable .toggleButtonPageTitle .fpp-icon-check{ 
    display:block;
}
.buttonTab .buttonPageTitleValue{
    display:inline-block;
    padding:0.5em;
    border:1px solid transparent;
    cursor:pointer;
    color:#171720;
}
.buttonTab.bb-active .buttonPageTitleValue{
    color:#fff;
}
.buttonTab.editable .buttonPageTitleValue{
    cursor: text;
}

@-webkit-keyframes scale-up-center {
  0% {
    -webkit-transform: scale(0.5);
            transform: scale(0.5);
  }
  100% {
    -webkit-transform: scale(1);
            transform: scale(1);
  }
}
@keyframes scale-up-center {
  0% {
    -webkit-transform: scale(0.5);
            transform: scale(0.5);
  }
  100% {
    -webkit-transform: scale(1);
            transform: scale(1);
  }
}
@keyframes success-animation {
  0% {
    background-position: right 20px top 39px;
    background-color:#2E4260;
    width: 150px;
    padding-right: 16px;
  }
  4% {
    background-position: right 20px top 14px;
    background-color:#56B760;
    width: 162px;
    padding-right: 40px;
  }
  96% {
    background-position: right 20px top 14px;
    background-color:#56B760;
    width: 162px;
    padding-right: 40px;
  }
  100% {
    background-position: right 20px top -10px;
    background-color:#2E4260;
    width: 150px;
    padding-right: 16px;
  }
}
.bbw-1 {
  width: 0%;
}

.bbw-2 {
  width: 1%;
}

.bbw-3 {
  width: 2%;
}

.bbw-4 {
  width: 3%;
}

.bbw-5 {
  width: 4%;
}

.bbw-6 {
  width: 5%;
}

.bbw-7 {
  width: 6%;
}

.bbw-8 {
  width: 7%;
}

.bbw-9 {
  width: 8%;
}

.bbw-10 {
  width: 9%;
}

.bbw-11 {
  width: 10%;
}

.bbw-12 {
  width: 11%;
}

.bbw-13 {
  width: 12%;
}

.bbw-14 {
  width: 13%;
}

.bbw-15 {
  width: 14%;
}

.bbw-16 {
  width: 15%;
}

.bbw-17 {
  width: 16%;
}

.bbw-18 {
  width: 17%;
}

.bbw-19 {
  width: 18%;
}

.bbw-20 {
  width: 19%;
}

.bbw-21 {
  width: 20%;
}

.bbw-22 {
  width: 21%;
}

.bbw-23 {
  width: 22%;
}

.bbw-24 {
  width: 23%;
}

.bbw-25 {
  width: 24%;
}

.bbw-26 {
  width: 25%;
}

.bbw-27 {
  width: 26%;
}

.bbw-28 {
  width: 27%;
}

.bbw-29 {
  width: 28%;
}

.bbw-30 {
  width: 29%;
}

.bbw-31 {
  width: 32.33%;
}

.bbw-32 {
  width: 32.33%;
}

.bbw-33 {
  width: 32.33%;
}

.bbw-34 {
  width: 32.33%;
}

.bbw-35 {
  width: 32.33%;
}

.bbw-36 {
  width: 32.33%;
}

.bbw-37 {
  width: 36%;
}

.bbw-38 {
  width: 37%;
}

.bbw-39 {
  width: 38%;
}

.bbw-40 {
  width: 39%;
}

.bbw-41 {
  width: 40%;
}

.bbw-42 {
  width: 41%;
}

.bbw-43 {
  width: 42%;
}

.bbw-44 {
  width: 43%;
}

.bbw-45 {
  width: 44%;
}

.bbw-46 {
  width: 45%;
}

.bbw-47 {
  width:49%;
}

.bbw-48 {
  width:49%;
}

.bbw-49 {
  width:49%;
}

.bbw-50 {
  width:49%;
}

.bbw-51 {
  width:49%;
}

.bbw-52 {
  width:49%;
}

.bbw-53 {
  width:49%;
}

.bbw-54 {
  width: 53%;
}

.bbw-55 {
  width: 54%;
}

.bbw-56 {
  width: 55%;
}

.bbw-57 {
  width: 56%;
}

.bbw-58 {
  width: 57%;
}

.bbw-59 {
  width: 58%;
}

.bbw-60 {
  width: 59%;
}

.bbw-61 {
  width: 60%;
}

.bbw-62 {
  width: 61%;
}

.bbw-63 {
  width: 62%;
}

.bbw-64 {
  width: 63%;
}

.bbw-65 {
  width: 65.666%;
}

.bbw-66 {
  width: 65.666%;
}

.bbw-67 {
  width: 65.666%;
}

.bbw-68 {
  width: 65.666%;
}

.bbw-69 {
  width: 65.666%;
}

.bbw-70 {
  width: 65.666%;
}

.bbw-71 {
  width: 70%;
}

.bbw-72 {
  width: 71%;
}

.bbw-73 {
  width: 72%;
}

.bbw-74 {
  width: 73%;
}

.bbw-75 {
  width: 74%;
}

.bbw-76 {
  width: 75%;
}

.bbw-77 {
  width: 76%;
}

.bbw-78 {
  width: 77%;
}

.bbw-79 {
  width: 78%;
}

.bbw-80 {
  width: 79%;
}

.bbw-81 {
  width: 80%;
}

.bbw-82 {
  width: 81%;
}

.bbw-83 {
  width: 82%;
}

.bbw-84 {
  width: 83%;
}

.bbw-85 {
  width: 84%;
}

.bbw-86 {
  width: 85%;
}

.bbw-87 {
  width: 86%;
}

.bbw-88 {
  width: 87%;
}

.bbw-89 {
  width: 88%;
}

.bbw-90 {
  width: 89%;
}

.bbw-91 {
  width: 90%;
}

.bbw-92 {
  width: 91%;
}

.bbw-93 {
  width: 92%;
}

.bbw-94 {
  width: 93%;
}

.bbw-95 {
  width: 94%;
}

.bbw-96 {
  width: 95%;
}

.bbw-97 {
  width: 96%;
}

.bbw-98 {
  width: 100%;
}

.bbw-99 {
  width: 100%;
}

.bbw-100 {
  width: 100%;
}

.bbh-1 {
  height: 10px;
}

.bbh-2 {
  height: 20px;
}

.bbh-3 {
  height: 30px;
}

.bbh-4 {
  height: 40px;
}

.bbh-5 {
  height: 50px;
}

.bbh-6 {
  height: 60px;
}

.bbh-7 {
  height: 70px;
}

.bbh-8 {
  height: 80px;
}

.bbh-9 {
  height: 90px;
}

.bbh-10 {
  height: 100px;
}

.bbh-11 {
  height: 110px;
}

.bbh-12 {
  height: 120px;
}

.bbh-13 {
  height: 130px;
}

.bbh-14 {
  height: 140px;
}

.bbh-15 {
  height: 150px;
}

.bbh-16 {
  height: 160px;
}

.bbh-17 {
  height: 170px;
}

.bbh-18 {
  height: 180px;
}

.bbh-19 {
  height: 190px;
}

.bbh-20 {
  height: 200px;
}

.bbh-21 {
  height: 210px;
}

.bbh-22 {
  height: 220px;
}

.bbh-23 {
  height: 230px;
}

.bbh-24 {
  height: 240px;
}

.bbh-25 {
  height: 250px;
}

.bbh-26 {
  height: 260px;
}

.bbh-27 {
  height: 270px;
}

.bbh-28 {
  height: 280px;
}

.bbh-29 {
  height: 290px;
}

.bbh-30 {
  height: 300px;
}

.bbh-31 {
  height: 310px;
}

.bbh-32 {
  height: 320px;
}

.bbh-33 {
  height: 330px;
}

.bbh-34 {
  height: 340px;
}

.bbh-35 {
  height: 350px;
}

.bbh-36 {
  height: 360px;
}

.bbh-37 {
  height: 370px;
}

.bbh-38 {
  height: 380px;
}

.bbh-39 {
  height: 390px;
}

.bbh-40 {
  height: 400px;
}
</style>