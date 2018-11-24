var datasources = [];

$(document).ready(function(){

	function setDatasources(datasourceId, lookupColumn, inputVal) {
		var murl = '/getdatasource/'+datasourceId+'/?c='+lookupColumn+'&q='+inputVal;

        $.get(murl, function(data) {
            if(data) {
                var obj = JSON.parse(data);
                var ds = obj.data;
                window.datasources[datasourceId] = ds;
            }
        });
	}

	if(window.Awesomplete) {
        var input = document.querySelector('input.autosuggest');
        var awesomplete = new Awesomplete(input);
        input.addEventListener("awesomplete-selectcomplete", function(ev) {});

        input.addEventListener('keyup', function(e) {
            var code = (e.keyCode || e.which);

            if (code === 37 || code === 38 || code === 39 || code === 40 || code === 27 || code === 13) {
                return;
            } else {
                var datasourceId = input.getAttribute('data-datasource-id');
                var lookupColumn = input.getAttribute('data-lookup-column');
                var murl = '/getdatasource/'+datasourceId+'/?c='+lookupColumn+'&q='+input.value;

                $.get(murl, function(data) {
                    if(data) {
                        var obj = JSON.parse(data);
                        var ds = obj.data;
                        var dsList = obj.dataList;
                        awesomplete.list = dsList;
                        window.datasources[datasourceId] = ds;
                    }
                });
            }
        });
	}

	setTimeout(function() {
		var form = $("#form");
		var original = form.serialize();
		var submitted = false;

		form.on("submit", function() {
			submitted = true;
		});

		window.onbeforeunload = function(){
	        if (form.serialize() != original && !submitted && form.data('leave-prompt') == 'on') {
				return 'Do you want to leave this form';
			}
	    }
	}, 0);

	$(document).on("change keyup input", "*", function() {
		execute_condition_display();
		execute_calculation();
		execute_calculation2();
		execute_textarea_maxlengths_check();
        execute_form_vars();
	});

	$(".awesomplete").on("awesomplete-select", function(e) {
		var input = $(this).find('input');
		var datasourceId = input.data('datasource-id');
        var lookupColumn = input.data('lookup-column');

        setDatasources(datasourceId, lookupColumn, e.text[lookupColumn]);

		execute_condition_display();
		execute_calculation();
		execute_calculation2();
		execute_textarea_maxlengths_check();
        execute_form_vars();
	});

	$(document).on("input", "input[type=range]", function() {
		var fieldset = $(this).closest('fieldset');
		fieldset.find('[fm-input-group=output-container] output').html($(this).val());
	});
	/*CONDITION DISPLAY*/
	execute_condition_display();

	function get(name){
	   if(name=(new RegExp('[?&]'+encodeURIComponent(name)+'=([^&]*)')).exec(location.search))
	      return decodeURIComponent(name[1]);
	}

    execute_form_vars();

    function execute_form_vars() {
        var fields = $(document).find('fieldset');
		fields.each(function() {
			var $this = $(this);
            var texts = $this.html();

			var regExp = /\{([^}]+)\}/g;
			var matched = texts.match(regExp);
			if(matched && matched.length) {
				var finalText=texts;
				var replaceable = false;
				for(var x=0; x<matched.length; x++) {
					var str = matched[x];
					var labelToFind = str.substring(1, str.length - 1);
					var found = false;
					fields.each(function() {
						var label = $(this).find('label').first();
						var innerTxt = label.html();

						if(labelToFind == innerTxt) {
							found = true;
							return;
						} else if($(this).attr('type') == 'LOOKUP') {
							var ipt = $(this).find('input').first();
							var columns = ipt.data('columns');
							if(columns && columns.indexOf(labelToFind) > -1) {
								found = true;
								return;
							}
						}
					});

					if(found) {
						replaceable = true;
						finalText = finalText.replace("{"+labelToFind+"}", '<span input-vars class="form_vars" data-label="'+labelToFind+'"></span>');
					}
				}

				if(replaceable) {
					$this.html(finalText);
				}

			}

        });

		setTimeout(function() {
			fields.each(function() {
				var form_var = $(this).find('[input-vars]');

				if(form_var.length) {
					form_var.each(function() {
						var label_container = $(this).data('label');
						var labels = label_container.split(',');
						$_this = $(this);

						var isInpt = false;
						if($_this.is("input")) {
							isInpt = true;
						}

						if(isInpt) {
							var tmpl = $_this.attr('input-vars-old');
						}

						var changes = 0;
						for(var ctr=0;ctr<labels.length; ctr++) {
							var labelToFind = labels[ctr];
							var found = false;

							var valToReplace = '';
							fields.each(function() {
								var label = $(this).find('label').first();
								var innerTxt = label.html();

								if(labelToFind == innerTxt) {
									found = true;
									changes++;
									var inpt = $(this).find('input');
									valToReplace = inpt.val();

									return;
								} else if($(this).attr('type') == 'LOOKUP') {
									var inpt = $(this).find('input');
									var lookupColumn = inpt.data('lookup-column');
									var datasource_id = inpt.data('datasource-id');

									var datasource = datasources[datasource_id];
									var columns = inpt.data('columns');
									if(datasource) {
										for(var ctr=0; ctr<datasource.length; ctr++) {
											var d = datasource[ctr];
											if(d[lookupColumn] == inpt.val()) {
												var dataArray = Object.values(d);
												var idx = columns.indexOf(labelToFind);
												if(idx > -1) {
													found = true;
													changes++;
													valToReplace = dataArray[idx];
													return;
												}
											}
										}
									}
								}
							});

							if(found) {
								if(isInpt) {
									newVal = tmpl.replace('<'+labelToFind+'>', valToReplace);
									tmpl = newVal;
								} else {
									newVal = valToReplace;
								}

							}
						}

						if(changes>0) {
							if(isInpt) {
								var attr = $_this.attr('input-vars-type');
								$_this.attr(attr, newVal);
							} else {
								$_this.html(newVal);
							}
						}
					});
				}
			});
		}, 0);
    }

	function execute_condition_display() {
		var fields = $(document).find('fieldset[data-logic]');
		fields.each(function() {
			var logic = $(this).data('logic');
			if(logic) {
				logic = logic.replace(/`/g, '"');
				logic = JSON.parse(logic);
                if(logic.conditions && logic.conditions.length) {
                    var conditions = logic.conditions;
                    var results = [];
                    $.each(conditions, function(idx, condition) {
                        var field_to_check = $(document).find('[name^="'+condition.if+'"]');
                        var field_to_check_val = field_to_check.val();

						if(field_to_check.length == 0) {
							var get_value = get(condition.if);
							if(get_value) {
								field_to_check_val = get_value;
							} else {
								field_to_check_val = '';
							}
						}

						if(field_to_check.attr('type') == 'radio') {
							field_to_check_val = $(document).find('[name="'+condition.if+'"]:checked').val();
							if(field_to_check_val) {
								field_to_check_val = field_to_check_val.toLowerCase();
							}
						} else if(field_to_check.attr('type') == 'checkbox') {
							field_to_check_val=[];
							values = $(document).find('[name^="'+condition.if+'"]:checked');
							values.each(function() {
							   field_to_check_val.push(this.value.toLowerCase());
							});

                            var sf = values;
                            var isProd=false;
                            if(sf.closest('fieldset').attr('type') == 'PRODUCTS') {
                                isProd=true;
                            }
                            if(isProd) {
                                var item = field_to_check.closest('.product_container');
                                var t = 0;
                                $.each(sf, function (i, f) {
                                    var v = $(f).val();
                                    if (isProd) {
                                        var lbl = $(f).closest('.product_container');
                                        price = lbl.find('.productPrice').html();
                                        var qty = item.find('.product_qty').val();
                                        if (!qty) {
                                            qty = 1;
                                        }
                                        var tot = price * qty;
                                        v = tot;
                                    }
                                    if (!isNaN(v)) {
                                        t += parseFloat(v);
                                    }
                                });
                                field_to_check_val = t;
                            }
						} else {
                            var isProd=false;
                            if(field_to_check.closest('fieldset').attr('type') == 'PRODUCTS') {
                                isProd=true;
                            }

                            if(isProd) {
                                if(field_to_check.val()) {
                                    var val = field_to_check.val().split("//");
                                    var price = val[2];
                                    if(isNaN(price)) { price = 0; }
                                    var item = field_to_check.closest('.product_container');
                                    var qty = item.find('.product_qty').val();
                                    if(!qty) {qty=1;}
                                    var tot=price*qty;

                                    field_to_check_val = tot;
                                }

                                field_to_check_val = field_to_check_val.toString();
                            }
						}

                        if(field_to_check_val==undefined) {
                            field_to_check_val='';
                        }

						if(field_to_check_val.length && field_to_check_val.constructor != Array) {
                            field_to_check_val = field_to_check_val.toString().replace(/"/g, ';;dq;;');
						}

                        if(condition.state == '=') {
							if(field_to_check_val.constructor === Array) {
								logic_condition = field_to_check_val.includes(condition.value.toLowerCase());
							} else {
								logic_condition = field_to_check_val.toLowerCase() == condition.value.toLowerCase();
							}
						} else if(field_to_check_val.constructor === Array) {
							logic_condition = false;
						} else {

							//check if value format is time
							var time_format = field_to_check_val.match(/-?([\d]+):([\d]+)/);
							if(time_format) {
								var hours = parseInt(time_format[0]);
								var minutes = parseFloat(time_format[2]/60);
								var time = hours + minutes;
								field_to_check_val = time;
							}

							if(condition.state == '>') {
								logic_condition = parseFloat(field_to_check_val) > parseFloat(condition.value);
							} else if(condition.state == '<') {
								logic_condition = parseFloat(field_to_check_val) < parseFloat(condition.value);
							} else if(condition.state == '>=') {
								logic_condition = parseFloat(field_to_check_val) >= parseFloat(condition.value);
							} else if(condition.state == '<=') {
								logic_condition = parseFloat(field_to_check_val) <= parseFloat(condition.value);
							} else if(condition.state == '!=') {
								logic_condition = field_to_check_val != condition.value;
							}
						}

                        results.push(logic_condition);
                    });

                    var exec_me = results.join(" || ");
                    if(logic.andor == 'ALL') {
                        exec_me = results.join(" && ");
                    }

                    var logic_result = eval(exec_me);

                    var pad_compact = $(this).closest('.pad-compact');
					var pad_halfs = pad_compact.find('.pad-half');
					var pad_halfs_ctr = 1;
					pad_halfs.each(function() {
						if($(this).css("display")!='none') {
							pad_halfs_ctr++;
						}
					});

					if(logic_result) {
						if(logic.action == 'show') {
							$(this).closest('.gc').show();
							$(this).closest('.flush').css('height', 'inherit');
							$(this).closest('.flush').removeClass('condition-hidden');
							pad_compact.css('padding', '5px 15px');
							pad_compact.removeClass('condition-hidden');
						} else {
							$(this).closest('.gc').hide();
							if(pad_halfs_ctr == 1) {
								$(this).closest('.flush').css('height', '0px');
								$(this).closest('.flush').addClass('condition-hidden');
								pad_compact.css('padding', '0px');
								pad_compact.addClass('condition-hidden');
							}
						}
					} else {
						if(logic.action == 'show') {
							$(this).closest('.gc').hide();
							if(pad_halfs_ctr == 1) {
								$(this).closest('.flush').css('height', '0px');
								$(this).closest('.flush').addClass('condition-hidden');
								pad_compact.css('padding', '0px');
								pad_compact.addClass('condition-hidden');
							}
						} else {
							$(this).closest('.gc').show();
							$(this).closest('.flush').css('height', 'inherit');
							$(this).closest('.flush').removeClass('condition-hidden');
							pad_compact.css('padding', '5px 15px');
							pad_compact.removeClass('condition-hidden');
						}
					}
                } else {
                    /*check if field match the value*/
					var field_to_check = $(document).find('[name^="'+logic.field+'"]');

					var logic_condition = '';

					var field_to_check_val = field_to_check.val();

					if(field_to_check.length == 0) {
						var get_value = get(logic.field);
						if(get_value) {
							field_to_check_val = get_value;
						} else {
							field_to_check_val = '';
						}
					}

					if(field_to_check.attr('type') == 'radio') {
						field_to_check_val = $(document).find('[name="'+logic.field+'"]:checked').val();
						if(field_to_check_val) {
							field_to_check_val = field_to_check_val.toLowerCase();
						}
					}

					if(field_to_check.attr('type') == 'checkbox') {
						field_to_check_val=[];
						values = $(document).find('[name^="'+logic.field+'"]:checked');
						values.each(function() {
						   field_to_check_val.push(this.value.toLowerCase());
						});
					}

                    if(field_to_check_val==undefined) {
                        field_to_check_val='';
                    }

					if(logic.condition == '=') {
						if(field_to_check_val.constructor === Array) {
							logic_condition = field_to_check_val.includes(logic.value.toLowerCase());
						} else {
							logic_condition = field_to_check_val.toLowerCase() == logic.value.toLowerCase();
						}
					} else if(field_to_check_val.constructor === Array) {
						logic_condition = false;
					} else {
						if(logic.condition == '>') {
							logic_condition = parseFloat(field_to_check_val) > parseFloat(logic.value);
						} else if(logic.condition == '<') {
							logic_condition = parseFloat(field_to_check_val) < parseFloat(logic.value);
						} else if(logic.condition == '>=') {
							logic_condition = parseFloat(field_to_check_val) >= parseFloat(logic.value);
						} else if(logic.condition == '<=') {
							logic_condition = parseFloat(field_to_check_val) <= parseFloat(logic.value);
						} else if(logic.condition == '!=') {
							logic_condition = field_to_check_val != logic.value;
						}
					}

					var pad_compact = $(this).closest('.pad-compact');
					var pad_halfs = pad_compact.find('.pad-half');
					var pad_halfs_ctr = 1;
					pad_halfs.each(function() {
						if($(this).css("display")!='none') {
							pad_halfs_ctr++;
						}
					});

					if(logic_condition) {
						if(logic.action == 'show') {
							$(this).closest('.gc').show();
							$(this).closest('.flush').css('height', 'inherit');
							$(this).closest('.flush').removeClass('condition-hidden');
							pad_compact.css('padding', '5px 15px');
							pad_compact.removeClass('condition-hidden');
						} else {
							$(this).closest('.gc').hide();
							if(pad_halfs_ctr == 1) {
								$(this).closest('.flush').css('height', '0px');
								$(this).closest('.flush').addClass('condition-hidden');
								pad_compact.css('padding', '0px');
								pad_compact.addClass('condition-hidden');
							}
						}
					} else {
						if(logic.action == 'show') {
							$(this).closest('.gc').hide();
							if(pad_halfs_ctr == 1) {
								$(this).closest('.flush').css('height', '0px');
								$(this).closest('.flush').addClass('condition-hidden');
								pad_compact.css('padding', '0px');
								pad_compact.addClass('condition-hidden');
							}
						} else {
							$(this).closest('.gc').show();
							$(this).closest('.flush').css('height', 'inherit');
							$(this).closest('.flush').removeClass('condition-hidden');
							pad_compact.css('padding', '5px 15px');
							pad_compact.removeClass('condition-hidden');
						}
					}
                }
			}
		});
	}

	/*END CONDITION DISPLAY*/

	/*CALCULATION*/
	execute_calculation();
    /*CALCULATION ELEMENT*/
	execute_calculation2();

	function fromLetters(str) {
	    var out = 0, len = str.length, pos = len;
	    while (--pos > -1) {
	        out += (str.charCodeAt(pos) - 64) * Math.pow(26, len - 1 - pos);
	    }
	    return out;
	}

	function execute_calculation() {
		var fields = $(document).find('fieldset[data-calc]');
		fields.each(function() {
            if($(this).attr('type') != 'CALCULATION') {
                var calc = $(this).data('calc');
				var calc_fields = $(this).attr('data-calc-fields');
				calc_fields = calc_fields.replace(/'/g, '"');
				calc_fields = JSON.parse(calc_fields);
				if(calc) {
					var chr=[];
					if(isNaN(calc)) {
						chr = calc.split('');
					}
					$.each(chr, function(i, ch) {
						var num_value = fromLetters(ch);
						if(num_value > 0 && num_value < 27) {
							var idx = num_value - 1;
							if(calc_fields.length) {
								field_to_check_val = 0;
								if(calc_fields[idx]) {
									var field = calc_fields[idx].field;
									if(field) {
										var field_to_check = $(document).find('[name^="'+field+'"]');

										if(field_to_check.attr('type') == 'radio') {
											var sf = $(document).find('[name="'+field+'"]:checked');
                                            var isProd=false;
                                            if(sf.closest('fieldset').attr('type') == 'PRODUCTS') {
                                                isProd=true;
                                            }
											if(sf.hasClass('other_input')) {
												field_to_check_val = sf.closest('label').find('input.other_option').val();
											} else {
												field_to_check_val = sf.val();
											}

                                            if(isProd) {
                                                if(sf.val()) {
                                                    var val = sf.val().split("//");
                                                    var price = val[2];
                                                    if(isNaN(price)) { price = 0; }
                                                    var item = field_to_check.closest('.product_container');
                                                    var qty = item.find('.product_qty').val();
                                                    if(!qty) {qty=1;}
                                                    total+=price*qty;

                                                    field_to_check_val = total;
                                                }
                                            }
										} else if(field_to_check.attr('type') == 'checkbox') {
											var sf = $(document).find('[name^="'+field+'"]:checked');
											var isProd=false;
											if(sf.closest('fieldset').attr('type') == 'PRODUCTS') {
												isProd=true;
											}
                                            var item = field_to_check.closest('.product_container');
											var t = 0;
											$.each(sf, function(i ,f) {
												var v = $(f).val();
												if(isProd) {
													var lbl = $(f).closest('.product_container');
													price=lbl.find('.productPrice').html();
                                                    var qty = item.find('.product_qty').val();
                                                    if(!qty) {qty=1;}
                                                    var tot=price*qty;
                                                    v=tot;
												}
												if(!isNaN(v)) { t+=parseFloat(v); }
											});
											field_to_check_val = t;
										} else {
											field_to_check_val = field_to_check.val();

                                            var isProd=false;
                                            if(field_to_check.closest('fieldset').attr('type') == 'PRODUCTS') {
                                                isProd=true;
                                            }

                                            if(isProd) {
                                                if(field_to_check.val()) {
                                                    var val = field_to_check.val().split("//");
                                                    var price = val[2];
                                                    if(isNaN(price)) { price = 0; }
                                                    var item = field_to_check.closest('.product_container');
                                                    var qty = item.find('.product_qty').val();
                                                    if(!qty) {qty=1;}
                                                    var tot=price*qty;

                                                    field_to_check_val = tot;
                                                }
                                            }
										}

										if(field_to_check.length == 0 || !field_to_check_val) { field_to_check_val = 0; }
										if(isNaN(field_to_check_val)) { field_to_check_val = 0; }
									}
								}

								calc = calc.replace(ch, field_to_check_val);
							}
						}
					});

					var result = eval(calc);
					if(result == 0) {
						var products = $('fieldset[type="PRODUCTS"][data-unit=currency]');
						var total=0;
						products.each(function() {
							var product = $(this);
							var items = product.find('[class*=product_container]');
							items.each(function() {
								var item = $(this);
								var inputCheck = item.find('.product_input');
								if(inputCheck.attr('type')=='checkbox' && inputCheck.is(":checked")) {
									var price = item.find('.productPrice').html();
									if(isNaN(price)) { price = 0; }
									var currency = item.find('.currency').html();
									var qty = item.find('.product_qty').val();
									if(!qty) {qty=1;}
									total+=price*qty;
								} else if(inputCheck.is('select')) {
									if(inputCheck.val()) {
										var val = inputCheck.val().split("//");
										var price = val[2];
										if(isNaN(price)) { price = 0; }
										var qty = item.find('.product_qty').val();
										if(!qty) {qty=1;}
										total+=price*qty;
									}
								}
							});
						});

						result = result + total;
					}

					$(this).find('input.totalHidden').val(result);

					if(result) {
						$(this).find('.total_container .total').html(result);
					} else {
						$(this).find('.total_container .total').html("0");
					}
				}
            }
		});
	}

    function execute_calculation2() {
		var fields = $(document).find('fieldset[data-calc]');
		fields.each(function() {
            if($(this).attr('type') == 'CALCULATION') {
                var calc = $(this).data('calc');
				var calc_fields = $(this).attr('data-calc-fields');
				//calc_fields = calc_fields.replace(/'/g, '"');
				calc_fields = JSON.parse(calc_fields);
				if(calc) {
					var chr=[];
					if(isNaN(calc)) {
						var isFormletsFunc = false;
						var formletsFunc = calc.substring(0,2);
						if(formletsFunc.toLowerCase() == 'fo') {
							var regex = /\(([^)]+)\)/;
							var matches = regex.exec(calc);
							var newCalc = matches[1];
							chr = newCalc.split(/[^A-Za-z]/);

							isFormletsFunc = true;
						} else {
							chr = calc.split(/[^A-Za-z]/);
						}
					}
					$.each(chr, function(i, ch) {
						var num_value = fromLetters(ch);
                        if(num_value > 0) {
							var idx = num_value - 1;
							if(calc_fields.length) {
								field_to_check_val = 0;
								if(calc_fields[idx]) {
									
									var field = calc_fields[idx].field;
									if(field) {
										var field_to_check = $(document).find('[name^="'+field+'"]');
										if(field_to_check.attr('type') == 'radio') {
											var sf = $(document).find('[name="'+field+'"]:checked');
											if(sf.hasClass('other_input')) {
												field_to_check_val = sf.closest('label').find('input.other_option').val();
											} else {
												field_to_check_val = sf.val();
											}
										} else if(field_to_check.attr('type') == 'checkbox') {
											var sf = $(document).find('[name^="'+field+'"]:checked');
											var isProd=false;
											if(sf.closest('fieldset').attr('type') == 'PRODUCTS') {
												isProd=true;
											}
											var t = 0;
											$.each(sf, function(i ,f) {
												var v = $(f).val();
												if(isProd) {
													var lbl = $(f).closest('label.option');
													v=lbl.find('.productPrice').html();
												}
												if(!isNaN(v)) { t+=parseFloat(v); }
											});
											field_to_check_val = t;
										} else {
											field_to_check_val = field_to_check.val();
										}

										if(field_to_check.length == 0 || !field_to_check_val) {
											field_to_check_val = '';
										} else {
											if(isFormletsFunc) {
												if(field_to_check.closest('fieldset').attr('type') == 'TIME') {
													var today = new Date().toISOString().slice(0, 10);
													field_to_check_val_orig = today + ' ' + field_to_check_val;
												} else {
													field_to_check_val_orig = field_to_check.closest('input').val();
												}

												field_to_check_val = Date.parse(field_to_check_val_orig);

												if(isNaN(field_to_check_val)) {
													field_to_check_val = field_to_check_val_orig;
												}
											}
										}

										if(isNaN(field_to_check_val)) {
											field_to_check_val = field_to_check_val.toString();
										}
									}
								}
                                if(isNaN(field_to_check_val)) {
                                    field_to_check_val = field_to_check_val.replace(/'/g, "\\'");
                                    calc = calc.replace(ch, "'"+field_to_check_val+"'");
                                } else {
                                    if(field_to_check_val) {
										if(newCalc) {
											newCalc = newCalc.replace(ch, field_to_check_val);
											calc = calc.replace(/\(([^)]+)\)/, '('+newCalc+')');
										} else {
											calc = calc.replace(ch, field_to_check_val);
										}
                                    } else {
                                    	if(newCalc) {
											newCalc = newCalc.replace(ch, null);
											calc = calc.replace(/\(([^)]+)\)/, '('+newCalc+')');
										} else {
											calc = calc.replace(ch, null);
										}
                                    }

                                }

							}
                        }
					});

					var result = eval(calc);
                    result = result.toString();

					$(this).find('input.totalHidden').val(result);

                    if($(this).attr('type') == 'CALCULATION' && !result) {
                        $(this).find('input.totalHidden').val('');
                    }

					if(result) {
						$(this).find('.total_container .total').html(result);
					} else {
						$(this).find('.total_container .total').html("0");
					}
				}
            }
		});
	}

	function execute_textarea_maxlengths_check() {
		var textareas = $(document).find('textarea[validate-maxlength]');
		textareas.each(function() {
			var fieldset = $(this).closest('fieldset');
			var maxlength = $(this).attr('max-char');
			var length = $(this).val().length;
			var rc = fieldset.find('.rc');
			rc.find('.remainingChar').html(length);

			if(parseInt(maxlength) < length) {
				rc.addClass('error');
			} else {
				rc.removeClass('error');
			}
		});
	}

	$(".datePicker, .datetimePicker").each(function(index, el) {
		var $el = $(el);
		var defaultDate = null;
		var beginDate = null;
		var endDate = null;
		var time = false;
		var format = $el.attr('dateFormat');
		var locale = $el.attr('dateLang');
		var interval = 1;
		var time_24hr = true;
		if($el.attr('interval')) {
			interval = $el.attr('interval');
		}
		if($el.attr('use12notation')) {
			time_24hr = $el.attr('use12notation') == 1 ? false:true;
		}

		if($el.hasClass('datetimePicker')) {
			time = true;

			if(time_24hr) {
				format = format + ' H:i';
			} else {
				format = format + ' h:i K';
			}
		}
		if($el.attr('defaultDate')) {
			defaultDate = new Date(window.strtotime($el.attr('defaultDate')) * 1000);
		}

		if($el.attr('beginDate')) {
			beginDate = new Date(window.strtotime($el.attr('beginDate')) * 1000);
		}

		if($el.attr('endDate')) {
			endDate = new Date(window.strtotime($el.attr('endDate')) * 1000);
		}


        var disabledDays = [];
        if($el.attr('disabledDays')) {
            disabledDays = $el.attr('disabledDays').split(',');
        }

        if(disabledDays.length) {
        	var opt = {
                dateFormat: format,
                minDate: beginDate,
                maxDate: endDate,
                enableTime:time,
                time_24hr:time_24hr,
                defaultDate:defaultDate,
                onClose: function(selectedDates, dateStr, instance) {
                    $(instance.input).blur();
                    var userAgent = navigator.userAgent || navigator.vendor || window.opera;
                    if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
                        var fieldsetId = $(instance.input).closest('fieldset').attr('id');
                        document.getElementById(fieldsetId).scrollIntoView();
                    }
                },
                "disable": [
                    function(date) {
                        // return true to disable
                        //return disabledDays && (date.getDay() === 0 || date.getDay() === 6);
                        return disabledDays && disabledDays.indexOf(date.getDay().toString()) !== -1;
                    }
                ],
                "locale": {
                    "firstDayOfWeek": 1 // start week on Monday
                }
            };

        	if(locale) {
        		opt.locale = locale;
			}

            flatpickr("#"+$el.attr('id'), opt);
        } else {
        	var opt = {
                dateFormat: format,
                minDate: beginDate,
                maxDate: endDate,
                enableTime:time,
                time_24hr:time_24hr,
                defaultDate:defaultDate,
                onClose: function(selectedDates, dateStr, instance) {
                    $(instance.input).blur();
                    var userAgent = navigator.userAgent || navigator.vendor || window.opera;
                    if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
                        var fieldsetId = $(instance.input).closest('fieldset').attr('id');
                        document.getElementById(fieldsetId).scrollIntoView();
                    }
                },
                "locale": {
                    "firstDayOfWeek": 1 // start week on Monday
                }
            };

            if(locale) {
                opt.locale = locale;
            }

            flatpickr("#"+$el.attr('id'), opt);
        }

	});

	$(".timePicker").each(function(index, el) {
		var $el = $(el);
		var interval = 1;
		var time_24hr = true;
        var beginDate = null;
		var endDate = null;
		if($el.attr('interval')) {
			interval = $el.attr('interval');
		}
		if($el.attr('use12notation')) {
			time_24hr = $el.attr('use12notation') == 1 ? false:true;
		}

        if($el.attr('minTime')) {
			beginDate = $el.attr('minTime');
		}
		if($el.attr('maxTime')) {
			endDate = $el.attr('maxTime');
		}

		if(time_24hr) {
			var format = 'H:i';
		} else {
			var format = 'h:i K';
		}
		flatpickr("#"+$el.attr('id'), {
			dateFormat:format,
			noCalendar:true,
			enableTime: true,
            minDate: beginDate,
            maxDate: endDate,
			minuteIncrement:interval,
			time_24hr:time_24hr,
			onClose: function(selectedDates, dateStr, instance) {
				$(instance.input).blur();
			}
		});
	});

	$(document).on("click", ".datetimePicker", function() {
		setTimeout(function() {
			$('.flatpickr-calendar').addClass('showTimeInput');
		}, 500);
	});

	function computeTotal() {
		var products = $('fieldset[type="PRODUCTS"][data-unit=currency]');
		var total=0;
		products.each(function() {
			var product = $(this);
			var items = product.find('[class*=product_container]');
			items.each(function() {
				var item = $(this);
				var inputCheck = item.find('.product_input');
				if(inputCheck.attr('type')=='checkbox' && inputCheck.is(":checked")) {
					var price = item.find('.productPrice').html();
					if(isNaN(price)) { price = 0; }
					var currency = item.find('.currency').html();
					var qty = item.find('.product_qty').val();
					if(!qty) {qty=1;}
					total+=price*qty;
				} else if(inputCheck.is('select')) {
					if(inputCheck.val()) {
						var val = inputCheck.val().split("//");
						var price = val[2];
						if(isNaN(price)) { price = 0; }
						var qty = item.find('.product_qty').val();
						if(!qty) {qty=1;}
						total+=price*qty;
					}
				}
			});
		});

		$(document).find('.total_container .total').html(total);
	};

	$(document).on("click", ".other_label", function() {
		var input = $(this).find('.other_input');
		var pcontainer = $(this).closest('.product_container');
		if(input.attr('type') == 'radio' || (input.attr('type') == 'checkbox' && input.is(":checked"))) {
			pcontainer.find('.other_option').focus();
		}
		computeTotal();
		execute_calculation();
	});

	$(document).on("change", ".product_input", function() {
		var pcontainer = $(this).closest('.product_container');
		if($(this).attr('type') == 'checkbox') {
			if($(this).is(":checked") == false) {
				pcontainer.find('.other_option').find('option').first().attr('selected', 'selected');
				pcontainer.find('.total').html('');
			} else {
				pcontainer.find('.other_option').val(1);
			}
		} else if($(this).is('select')) {
			if($(this).val()) {
				pcontainer.find('.other_option').val(1);
				pcontainer.find('.other_option').trigger('change');
			} else {
				pcontainer.find('.other_option').find('option').first().attr('selected', 'selected');
				pcontainer.find('.total').html('');
			}
		}

		computeTotal();
		execute_calculation();
	});

	$(document).on("change", ".product_container .other_option", function() {
		var pcontainer = $(this).closest('.product_container');
		var price = pcontainer.find('.productPrice').html();
		if(isNaN(price)) { price = 0; }
		if(price==null) {
			var product = pcontainer.find('.product_input');
			if(!product) {
				price = 0;
			} else {
				if(product.val()) {
					var arr = product.val().split('//');
					price = arr[2];
				} else {
					price = 0;
				}
			}
		}
		var currency = pcontainer.find('.currency').html();
		var currencySymbol = pcontainer.find('.currencySymbol').html();
		var qty = $(this).val() ? $(this).val():1;
		var new_price = price * qty;

		pcontainer.find('.total').html(currencySymbol+new_price + ' ' + currency);

		computeTotal();
		execute_calculation();
	});

	$(document).on("focus", ".other_option", function() {
		var pcontainer = $(this).closest('.product_container');
		var other_input = pcontainer.find('.other_input');
		if(!other_input.is(":checked")) {
			other_input.trigger('click');
		}
		$(this).trigger('change');
		$(this).on("keyup change", function() {
			if(other_input.hasClass('product_input') == false) {
				other_input.val($(this).val());
			}
		});
	});

    $('.rating input').change(function () {
        var $rating = $(this).closest('.rating');
        var $radio = $(this);
        $rating.find('.selected').removeClass('selected');
        $radio.closest('label').addClass('selected');
    });

    $('input[type="phones"]').mask("(000) 000-0000", {
        placeholder: "(___) ___-____",
        clearIfNotMatch:false
    });
    $('input[validate-phone8]').mask("0000-0000",{
        placeholder: "____-____",
        clearIfNotMatch:false
    });
    $('input[validate-phone10]').mask("0000-00-00-00",{
        placeholder: "____-__-__-__",
        clearIfNotMatch:false
    });
    $('input[validate-phone13]').mask("0000-000-00-00-00",{
        placeholder: "____-___-__-__-__",
        clearIfNotMatch:false
    });
    $('input[type="dates"]').mask("00/00/0000", {
        placeholder: "__/__/____",
        clearIfNotMatch:false
    });

    var elements = $('[type="range"]');
    elements.each(function(){
        var $el = $(this);
        var $root = $el.closest('fieldset');
        var $err = $root.find('.error');
        var $output = $root.find('output');
        var $tooltip = $root.find('[fm-module="tooltip-range"]');
        var $value = $tooltip.find('p');
        $output.val($el.val());
        $value.html($el.val());
        $tooltip.css({
            left: ($el.val() / $el.attr('max') * 100) + '%'
        });
        $el.on('input', function(){
            $output.val($el.val());
            $value.html($el.val());
            $tooltip.css({
                left: ($el.val() / $el.attr('max') * 100) + '%'
            });
        });
        $el.on('mousedown', function(){
            $tooltip.addClass('show');
        });
        $el.on('mouseup mouseleave', function(){
            $tooltip.removeClass('show');
        });
    });
});


var recaptcha_success = false;

function check_captcha(response) {
    var captchaField = $(document).find('fieldset[type=CAPTCHA]');
    if(captchaField.length) {
        var captchaErrorMessage = captchaField.find('.g-recaptcha').data('error');
        var resp = grecaptcha.getResponse();
        if(resp.length == 0) {
            captchaField.addClass('req-error');
            captchaField.append('<div class="help field-error align-center">'+captchaErrorMessage+'</div>');
            return false;
        } else {
            captchaField.removeClass('req-error');
            captchaField.find('.field-error').remove();
            return true;
        }
    } else {
        return true;
    }
}

function isVis(ele) {
    if(ele.css('display')!='none' && ele.css('visibility')!='hidden' && ele.height()>0) {
        return true;
    } else {
        return false;
    }
}

function checkElement(el, type) {

    var hasError = false;
    var required_message = window.global_required_message;
    var el = $(el);
    var required = el.attr('validate-required');
    var invalid_message = el.attr('error-message');
    var val = el.val();

    if(el.is('[type=checkbox]') || el.is('[type=radio]')) {
        val = '';
        el.each(function(i, e) {
            if($(e).is(':checked')) {
                val = 'x';
            }
        })
    }

    var toBeCheck = false;
    if(type == 'change' && el.hasClass('dirty')) {
        toBeCheck = true;
    } else if(type == 'submit') {
        toBeCheck = true;
        el.addClass('dirty');
    }

    if(isVis(el.closest('.gc')) == false) {
        toBeCheck = false;
    }

    if(toBeCheck) {
        var fieldset = el.closest('fieldset');
        fieldset.find('.help.field-error').remove();
        fieldset.removeClass('req-error');
        if(el.is("[large-file]") && !el.is("[validate-required]")) {
            var unfinishUpload = el.attr('unfinishupload');
            var finishUpload = el.attr('finishupload');
            var uploading = el.attr('uploading');

            var file = el.closest('div.file');
            if($.trim(file.find('input.hidden_file').val()) == '' && file.find('.loading_indicator').html()) {
                fieldset.addClass('req-error');
                if(fieldset.find('p.help').length) {
                    $('<div class="help field-error">'+unfinishUpload+'</div>').insertBefore(fieldset.find('p.help'));
                } else {
                    fieldset.append('<div class="help field-error">'+unfinishUpload+'</div>');
                }

                hasError = true;
            }
        } else if(el.is("[validate-required]") && el.is("[large-file]")) {
            var unfinishUpload = el.attr('unfinishupload');
            var finishUpload = el.attr('finishupload');
            var uploading = el.attr('uploading');

            var file = el.closest('div.file');
            if($.trim(file.find('input.hidden_file').val()) == '') {
                fieldset.addClass('req-error');
                if(fieldset.find('p.help').length) {
                    $('<div class="help field-error">'+unfinishUpload+'</div>').insertBefore(fieldset.find('p.help'));
                } else {
                    fieldset.append('<div class="help field-error">'+unfinishUpload+'</div>');
                }

                hasError = true;
            }
        } else if(el.is("[validate-required]") && el.is("[lookup]") && $.trim(val) != '') {
            var invalidValue = el.attr('invalid-value');
            var datasourceId = el.data('datasource-id');

            if(window.datasources[datasourceId] && window.datasources[datasourceId].length != 1) {
            	fieldset.addClass('req-error');
                if(fieldset.find('p.help').length) {
                    $('<div class="help field-error">'+invalidValue+'</div>').insertBefore(fieldset.find('p.help'));
                } else {
                    fieldset.append('<div class="help field-error">'+invalidValue+'</div>');
                }

                hasError = true;
            }
            
        } else if (el.is("[validate-required]") && $.trim(val) == '') {
            fieldset.addClass('req-error');
            if(fieldset.find('p.help').length) {
                $('<div class="help field-error">'+required_message+'</div>').insertBefore(fieldset.find('p.help'));
            } else {
                fieldset.append('<div class="help field-error">'+required_message+'</div>');
            }

            hasError = true;
        } else if(el.is("[validate-email]")) {
            var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            if(regex.test(val) == false && $.trim(val) != '') {
                fieldset.addClass('req-error');
                if(invalid_message) {
                    if(fieldset.find('p.help').length) {
                        $('<div class="help field-error">'+invalid_message+'</div>').insertBefore(fieldset.find('p.help'));
                    } else {
                        fieldset.append('<div class="help field-error">'+invalid_message+'</div>');
                    }
                } else {
                    if(fieldset.find('p.help').length) {
                        $('<div class="help field-error">This must be a valid email address.</div>').insertBefore(fieldset.find('p.help'));
                    } else {
                        fieldset.append('<div class="help field-error">This must be a valid email address.</div>');
                    }
                }
                hasError = true;
            }
        } else if(el.is("[validate-number]")) {
            var regex = /^\d+$/;
            if(regex.test(val) == false && $.trim(val) != '') {
                fieldset.addClass('req-error');
                if(invalid_message) {
                    if(fieldset.find('p.help').length) {
                        $('<div class="help field-error">'+invalid_message+'</div>').insertBefore(fieldset.find('p.help'));
                    } else {
                        fieldset.append('<div class="help field-error">'+invalid_message+'</div>');
                    }
                } else {
                    if(fieldset.find('p.help').length) {
                        $('<div class="help field-error">This must be a valid number.</div>').insertBefore(fieldset.find('p.help'));
                    } else {
                        fieldset.append('<div class="help field-error">This must be a valid number.</div>');
                    }
                }
                hasError = true;
            }
        } else if(el.is("[validate-regex]")) {
            try {
                var regex_val = el.attr('regex');
                var flags = regex_val.replace(/.*\/([gimy]*)$/, '$1');
                var pattern = regex_val.replace(new RegExp('^/(.*?)/'+flags+'$'), '$1');
                var regex = new RegExp(pattern, flags);
                if(regex.test(val) == false && $.trim(val) != '') {
                    fieldset.addClass('req-error');
                    if(invalid_message) {
                        if(fieldset.find('p.help').length) {
                            $('<div class="help field-error">'+invalid_message+'</div>').insertBefore(fieldset.find('p.help'));
                        } else {
                            fieldset.append('<div class="help field-error">'+invalid_message+'</div>');
                        }
                    } else {
                        if(fieldset.find('p.help').length) {
                            $('<div class="help field-error">The value does not match the regular expression</div>').insertBefore(fieldset.find('p.help'));
                        } else {
                            fieldset.append('<div class="help field-error">The value does not match the regular expression</div>');
                        }
                    }
                    hasError = true;
                }
            } catch(e) {

            }
        } else if(el.is("[validate-maxlength]")) {
            var max = el.attr('max-char');
            if(val.length > max && $.trim(val) != '') {
                fieldset.addClass('req-error');
                if(invalid_message) {
                    if(fieldset.find('p.help').length) {
                        $('<div class="help field-error">'+invalid_message+'</div>').insertBefore(fieldset.find('p.help'));
                    } else {
                        fieldset.append('<div class="help field-error">'+invalid_message+'</div>');
                    }
                } else {
                    if(fieldset.find('p.help').length) {
                        $('<div class="help field-error">This field exceeded the maximum characters allowed.</div>').insertBefore(fieldset.find('p.help'));
                    } else {
                        fieldset.append('<div class="help field-error">This field exceeded the maximum characters allowed.</div>');
                    }
                }
                hasError = true;
            }
        }
    }

    return hasError;
}
function checkValidation(type) {
    var page = $(".page").not(".hidden");

    var elements = [];
    var hasError = false;

    page.find('input, textarea, select').each(function(i, el) {
        if($(el).not('[disabled]')) {
            var found = $.inArray($(el).attr('name'), elements);
            if(found == -1 && !$(el).hasClass('no-validate')) {
                elements.push($(el).attr('name'));
            }
        }
    });

    $.each(elements, function(i, element) {
        var els = page.find('[name="'+element+'"]');

        if(els.length > 1 && (els.attr('type') != 'checkbox' && els.attr('type') != 'radio')) {
            $.each(els, function(i, el) {
                if(!hasError) {
                    hasError = checkElement(el, type);
                } else {
                    checkElement(el, type);
                }
            });
        } else {
            if(!hasError) {
                hasError = checkElement(els, type);
            } else {
                checkElement(els, type);
            }
        }

    });

    return hasError;
}
