var validator=(function($){var message,tests,checkField,validate,mark,unmark,field,minmax,defaults,validateWords,lengthRange,lengthLimit,pattern,alertTxt,data,email_illegalChars=/[\(\)\<\>\,\;\:\\\/\"\[\]]/,email_filter=/^.+@.+\..{2,6}$/;message={invalid:'invalid input',checked:'must be checked',empty:'please put something here',min:'input is too short',max:'input is too long',number_min:'too low',number_max:'too high',url:'invalid URL',number:'not a number',email:'email address is invalid',email_repeat:'emails do not match',password_repeat:'passwords do not match',repeat:'no match',complete:'input is not complete',select:'Please select an option'};if(!window.console){console={};console.log=console.warn=function(){return;}}
defaults={alerts:true,classes:{item:'item',alert:'alert',bad:'bad'}};tests={sameAsPlaceholder:function(a){return $.fn.placeholder&&a.attr('placeholder')!==undefined&&data.val==a.prop('placeholder');},hasValue:function(a){if(!a){alertTxt=message.empty;return false;}
return true;},linked:function(a,b){if(b!=a){alertTxt=message[data.type+'_repeat']||message.no_match;return false;}
return true;},email:function(a){if(!email_filter.test(a)||a.match(email_illegalChars)){alertTxt=a?message.email:message.empty;return false;}
return true;},text:function(a,skip){if(validateWords){var words=a.split(' ');var wordsLength=function(len){for(var w=words.length;w--;)
if(words[w].length<len)
return false;return true;};if(words.length<validateWords||!wordsLength(2)){alertTxt=message.complete;return false;}
return true;}
if(!skip&&lengthRange&&a.length<lengthRange[0]){alertTxt=message.min;return false;}
if(lengthRange&&lengthRange[1]&&a.length>lengthRange[1]){alertTxt=message.max;return false;}
if(lengthLimit&&lengthLimit.length){while(lengthLimit.length){if(lengthLimit.pop()==a.length){alertTxt=message.complete;return false;}}}
if(pattern){var regex,jsRegex;switch(pattern){case 'alphanumeric':regex=/^[a-zA-Z0-9]+$/i;break;case 'numeric':regex=/^[0-9]+$/i;break;case 'phone':regex=/^\+?([0-9]|[-|' '])+$/i;break;default:regex=pattern;}
try{jsRegex=new RegExp(regex).test(a);if(a&&!jsRegex)
return false;}
catch(err){console.log(err,field,'regex is invalid');return false;}}
return true;},number:function(a){if(isNaN(parseFloat(a))&&!isFinite(a)){alertTxt=message.number;return false;}
else if(lengthRange&&a.length<lengthRange[0]){alertTxt=message.min;return false;}
else if(lengthRange&&lengthRange[1]&&a.length>lengthRange[1]){alertTxt=message.max;return false;}
else if(minmax[0]&&(a|0)<minmax[0]){alertTxt=message.number_min;return false;}
else if(minmax[1]&&(a|0)>minmax[1]){alertTxt=message.number_max;return false;}
return true;},date:function(a){var day,A=a.split(/[-./]/g),i;if(field[0].valueAsNumber)
return true;for(i=A.length;i--;){if(isNaN(parseFloat(a))&&!isFinite(a))
return false;}
try{day=new Date(A[2],A[1]-1,A[0]);if(day.getMonth()+1==A[1]&&day.getDate()==A[0])
return day;return false;}
catch(er){console.log('date test: ',err);return false;}},url:function(a){function testUrl(url){return /^(https?:\/\/)?([\w\d\-_]+\.+[A-Za-z]{2,})+\/?/.test(url);}
if(!testUrl(a)){alertTxt=a?message.url:message.empty;return false;}
return true;},hidden:function(a){if(lengthRange&&a.length<lengthRange[0]){alertTxt=message.min;return false;}
if(pattern){var regex;if(pattern=='alphanumeric'){regex=/^[a-z0-9]+$/i;if(!regex.test(a)){return false;}}}
return true;},select:function(a){if(!tests.hasValue(a)){alertTxt=message.select;return false;}
return true;}};mark=function(field,text){if(!text||!field||!field.length)
return false;var item=field.closest('.'+defaults.classes.item),warning;if(item.hasClass(defaults.classes.bad)){if(defaults.alerts)
item.find('.'+defaults.classes.alert).html(text);}
else if(defaults.alerts){warning=$('<div class="'+defaults.classes.alert+'">').html(text);item.append(warning);}
item.removeClass(defaults.classes.bad);setTimeout(function(){item.addClass(defaults.classes.bad);},0);};unmark=function(field){if(!field||!field.length){console.warn('no "field" argument, null or DOM object not found');return false;}
field.closest('.'+defaults.classes.item).removeClass(defaults.classes.bad).find('.'+defaults.classes.alert).remove();};function testByType(type,value){if(type=='tel')
pattern=pattern||'phone';if(!type||type=='password'||type=='tel'||type=='search'||type=='file')
type='text';return tests[type]?tests[type](value,true):true;}
function prepareFieldData(el){field=$(el);field.data('valid',true);field.data('type',field.attr('type'));pattern=field.attr('pattern');}
function keypress(e){prepareFieldData(this);if(e.charCode){return testByType(this.type,this.value);}}
function checkField(){if(this.type!='hidden'&&$(this).is(':hidden'))
return true;prepareFieldData(this);field.data('val',field[0].value.replace(/^\s+|\s+$/g,""));data=field.data();alertTxt=message[field.prop('name')]||message.invalid;if(field[0].nodeName.toLowerCase()==="select"){data.type='select';}
else if(field[0].nodeName.toLowerCase()==="textarea"){data.type='text';}
validateWords=data['validateWords']||0;lengthRange=data['validateLengthRange']?(data['validateLengthRange']+'').split(','):[1];lengthLimit=data['validateLength']?(data['validateLength']+'').split(','):false;minmax=data['validateMinmax']?(data['validateMinmax']+'').split(','):'';data.valid=tests.hasValue(data.val);if(field.hasClass('optional')&&!data.valid)
data.valid=true;if(field[0].type==="checkbox"){data.valid=field[0].checked;alertTxt=message.checked;}
else if(data.valid){if(tests.sameAsPlaceholder(field)){alertTxt=message.empty;data.valid=false;}
if(data.validateLinked){var linkedTo=data['validateLinked'].indexOf('#')==0?$(data['validateLinked']):$(':input[name='+data['validateLinked']+']');data.valid=tests.linked(data.val,linkedTo.val());}
else if(data.valid||data.type=='select')
data.valid=testByType(data.type,data.val);}
if(data.valid)
unmark(field);else{mark(field,alertTxt);submit=false;}
return data.valid;}
function checkAll($form){$form=$($form);if($form.length==0){console.warn('element not found');return false;}
var that=this,submit=true,fieldsToCheck=$form.find(':input').filter('[required=required], .required, .optional').not('[disabled=disabled]');fieldsToCheck.each(function(){submit=submit*checkField.apply(this);});return!!submit;}
return{defaults:defaults,checkField:checkField,keypress:keypress,checkAll:checkAll,mark:mark,unmark:unmark,message:message,tests:tests}})(jQuery);