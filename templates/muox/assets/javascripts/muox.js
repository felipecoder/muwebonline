function LoadRanking(t) {
  LoadingEffect(), LoadContent(t)
}
var tab = {
      init: function() {
          $("#j_tab li a").click(function() {
              return !1
          }), $("#j_tab li a").live("mouseover", function() {
              var t = $(this).attr("id"),
                  e = $(this).attr("id-div"),
                  i = $("#" + $(this).attr("id-div"));
              $(this).hasClass("active") || ($('#j_tab li a[id-div="' + e + '"]').removeClass("active"), $(this).addClass("active"), i.find('div[id!="' + t + '"]').hide(0, function() {
                  i.find('div[id="' + t + '"]').fadeIn(0)
              }))
          })
      }
  },
  slideShow = {
      autoTime: 0,
      init: function() {
          slideShow.autoPlay($("#eye_number a.on"));
          var t = $("#eye_box a:eq(0) img:eq(0)");
          t.attr("src", t.attr("dataimgsrc")), t.load(function() {
              slideShow.autoTime = setTimeout(function() {
                  slideShow.autoPlay()
              }, 6e3)
          }), $("#eye_number a").live("mouseover", function() {
              $(this).attr("class").indexOf("on") > 0 || slideShow.autoPlay(this)
          })
      },
      autoPlay: function(t) {
          clearTimeout(this.autoTime), this.turnNumber(t);
          var e = $("#eye_number a.on").index(),
              i = $("#eye_box a").eq(e).children("img");
          "" == i.attr("src") && i.attr("src", i.attr("dataimgsrc")), setTimeout(function() {
              $("#eye_box a:visible").fadeOut(0, function() {
                  $("#eye_box a").eq(e).fadeIn(0)
              })
          }, 200), this.autoTime = setTimeout("slideShow.autoPlay()", 6e3)
      },
      turnNumber: function(t) {
          if (void 0 === t) {
              var e = $("#eye_number a.on").index();
              e = e >= $("#eye_number a").length - 1 ? 0 : e + 1, t = $("#eye_number a").eq(e)
          }
          $("#eye_number a.on").each(function() {
              $(this).removeClass("on").addClass("off")
          }), $(t).removeClass("off").addClass("on")
      }
  },
  modal = {
      init: function() {
          $("#j_modal_close").live("click", function() {
              var t = $(this).attr("div-close");
              return $("#" + t).find(".main_modal_content").fadeOut(100, function() {
                  $(".main_modal").fadeOut(0)
              }), !1
          }), $("#j_modal").live("click", function() {
              var t = $(this).attr("div-open");
              return $(".main_modal").fadeIn(0, function() {
                  $("#" + t).find(".main_modal_content").fadeIn(100)
              }), !1
          })
      }
  };
! function(t) {
  t("#j_control").live("click", function(e) {
      e.preventDefault();
      var i = t("#" + t(this).attr("id-div"));
      t(this).toggleClass("control"), t(this).hasClass("control") ? t(this).html("Ocultar") : t(this).html("Mostrar"), i.slideToggle(50)
  }), tab.init(), slideShow.init(), modal.init()
}(jQuery);;
! function(e) {
  "use strict";

  function t(t) {
      var a = t.data;
      t.isDefaultPrevented() || (t.preventDefault(), e(this).ajaxSubmit(a))
  }

  function a(t) {
      var a = t.target,
          r = e(a);
      if (!r.is(":submit,input:image")) {
          var n = r.closest(":submit");
          if (0 === n.length) return;
          a = n[0]
      }
      var s = this;
      if (s.clk = a, "image" == a.type)
          if (void 0 !== t.offsetX) s.clk_x = t.offsetX, s.clk_y = t.offsetY;
          else if ("function" == typeof e.fn.offset) {
          var i = r.offset();
          s.clk_x = t.pageX - i.left, s.clk_y = t.pageY - i.top
      } else s.clk_x = t.pageX - a.offsetLeft, s.clk_y = t.pageY - a.offsetTop;
      setTimeout(function() {
          s.clk = s.clk_x = s.clk_y = null
      }, 100)
  }

  function r() {
      if (e.fn.ajaxSubmit.debug) {
          var t = "[jquery.form] " + Array.prototype.join.call(arguments, "");
          window.console && window.console.log ? window.console.log(t) : window.opera && window.opera.postError && window.opera.postError(t)
      }
  }
  var n = {};
  n.fileapi = void 0 !== e("<input type='file'/>").get(0).files, n.formdata = void 0 !== window.FormData, e.fn.ajaxSubmit = function(t) {
      function a(a) {
          function n(e) {
              return e.contentWindow ? e.contentWindow.document : e.contentDocument ? e.contentDocument : e.document
          }

          function i() {
              function t() {
                  try {
                      var e = n(g).readyState;
                      r("state = " + e), e && "uninitialized" == e.toLowerCase() && setTimeout(t, 50)
                  } catch (e) {
                      r("Server abort: ", e, " (", e.name, ")"), o(A), k && clearTimeout(k), k = void 0
                  }
              }
              var a = l.attr("target"),
                  i = l.attr("action");
              _.setAttribute("target", p), s || _.setAttribute("method", "POST"), i != h.url && _.setAttribute("action", h.url), h.skipEncodingOverride || s && !/post/i.test(s) || l.attr({
                  encoding: "multipart/form-data",
                  enctype: "multipart/form-data"
              }), h.timeout && (k = setTimeout(function() {
                  b = !0, o(T)
              }, h.timeout));
              var u = [];
              try {
                  if (h.extraData)
                      for (var c in h.extraData) h.extraData.hasOwnProperty(c) && u.push(e('<input type="hidden" name="' + c + '">').attr("value", h.extraData[c]).appendTo(_)[0]);
                  h.iframeTarget || (m.appendTo("body"), g.attachEvent ? g.attachEvent("onload", o) : g.addEventListener("load", o, !1)), setTimeout(t, 15), _.submit()
              } finally {
                  _.setAttribute("action", i), a ? _.setAttribute("target", a) : l.removeAttr("target"), e(u).remove()
              }
          }

          function o(t) {
              if (!v.aborted && !C) {
                  try {
                      E = n(g)
                  } catch (e) {
                      r("cannot access response document: ", e), t = A
                  }
                  if (t === T && v) v.abort("timeout");
                  else if (t == A && v) v.abort("server abort");
                  else if (E && E.location.href != h.iframeSrc || b) {
                      g.detachEvent ? g.detachEvent("onload", o) : g.removeEventListener("load", o, !1);
                      var a, s = "success";
                      try {
                          if (b) throw "timeout";
                          var i = "xml" == h.dataType || E.XMLDocument || e.isXMLDoc(E);
                          if (r("isXml=" + i), !i && window.opera && (null === E.body || !E.body.innerHTML) && --L) return r("requeing onLoad callback, DOM not available"), void setTimeout(o, 250);
                          var l = E.body ? E.body : E.documentElement;
                          v.responseText = l ? l.innerHTML : null, v.responseXML = E.XMLDocument ? E.XMLDocument : E, i && (h.dataType = "xml"), v.getResponseHeader = function(e) {
                              return {
                                  "content-type": h.dataType
                              }[e]
                          }, l && (v.status = Number(l.getAttribute("status")) || v.status, v.statusText = l.getAttribute("statusText") || v.statusText);
                          var u = (h.dataType || "").toLowerCase(),
                              c = /(json|script|text)/.test(u);
                          if (c || h.textarea) {
                              var f = E.getElementsByTagName("textarea")[0];
                              if (f) v.responseText = f.value, v.status = Number(f.getAttribute("status")) || v.status, v.statusText = f.getAttribute("statusText") || v.statusText;
                              else if (c) {
                                  var p = E.getElementsByTagName("pre")[0],
                                      y = E.getElementsByTagName("body")[0];
                                  p ? v.responseText = p.textContent ? p.textContent : p.innerText : y && (v.responseText = y.textContent ? y.textContent : y.innerText)
                              }
                          } else "xml" == u && !v.responseXML && v.responseText && (v.responseXML = D(v.responseText));
                          try {
                              S = M(v, u, h)
                          } catch (t) {
                              s = "parsererror", v.error = a = t || s
                          }
                      } catch (t) {
                          r("error caught: ", t), s = "error", v.error = a = t || s
                      }
                      v.aborted && (r("upload aborted"), s = null), v.status && (s = v.status >= 200 && v.status < 300 || 304 === v.status ? "success" : "error"), "success" === s ? (h.success && h.success.call(h.context, S, "success", v), d && e.event.trigger("ajaxSuccess", [v, h])) : s && (void 0 === a && (a = v.statusText), h.error && h.error.call(h.context, v, s, a), d && e.event.trigger("ajaxError", [v, h, a])), d && e.event.trigger("ajaxComplete", [v, h]), d && !--e.active && e.event.trigger("ajaxStop"), h.complete && h.complete.call(h.context, v, s), C = !0, h.timeout && clearTimeout(k), setTimeout(function() {
                          h.iframeTarget || m.remove(), v.responseXML = null
                      }, 100)
                  }
              }
          }
          var u, c, h, d, p, m, g, v, y, x, b, k, _ = l[0],
              w = !!e.fn.prop;
          if (e(":input[name=submit],:input[id=submit]", _).length) alert('Error: Form elements must not have name or id of "submit".');
          else {
              if (a)
                  for (c = 0; c < f.length; c++) u = e(f[c]), w ? u.prop("disabled", !1) : u.removeAttr("disabled");
              if (h = e.extend(!0, {}, e.ajaxSettings, t), h.context = h.context || h, p = "jqFormIO" + (new Date).getTime(), h.iframeTarget ? (x = (m = e(h.iframeTarget)).attr("name")) ? p = x : m.attr("name", p) : (m = e('<iframe name="' + p + '" src="' + h.iframeSrc + '" />')).css({
                      position: "absolute",
                      top: "-1000px",
                      left: "-1000px"
                  }), g = m[0], v = {
                      aborted: 0,
                      responseText: null,
                      responseXML: null,
                      status: 0,
                      statusText: "n/a",
                      getAllResponseHeaders: function() {},
                      getResponseHeader: function() {},
                      setRequestHeader: function() {},
                      abort: function(t) {
                          var a = "timeout" === t ? "timeout" : "aborted";
                          r("aborting upload... " + a), this.aborted = 1, m.attr("src", h.iframeSrc), v.error = a, h.error && h.error.call(h.context, v, a, t), d && e.event.trigger("ajaxError", [v, h, a]), h.complete && h.complete.call(h.context, v, a)
                      }
                  }, (d = h.global) && 0 == e.active++ && e.event.trigger("ajaxStart"), d && e.event.trigger("ajaxSend", [v, h]), h.beforeSend && !1 === h.beforeSend.call(h.context, v, h)) h.global && e.active--;
              else if (!v.aborted) {
                  (y = _.clk) && (x = y.name) && !y.disabled && (h.extraData = h.extraData || {}, h.extraData[x] = y.value, "image" == y.type && (h.extraData[x + ".x"] = _.clk_x, h.extraData[x + ".y"] = _.clk_y));
                  var T = 1,
                      A = 2,
                      j = e("meta[name=csrf-token]").attr("content"),
                      R = e("meta[name=csrf-param]").attr("content");
                  R && j && (h.extraData = h.extraData || {}, h.extraData[R] = j), h.forceSync ? i() : setTimeout(i, 10);
                  var S, E, C, L = 50,
                      D = e.parseXML || function(e, t) {
                          return window.ActiveXObject ? ((t = new ActiveXObject("Microsoft.XMLDOM")).async = "false", t.loadXML(e)) : t = (new DOMParser).parseFromString(e, "text/xml"), t && t.documentElement && "parsererror" != t.documentElement.nodeName ? t : null
                      },
                      F = e.parseJSON || function(e) {
                          return window.eval("(" + e + ")")
                      },
                      M = function(t, a, r) {
                          var n = t.getResponseHeader("content-type") || "",
                              s = "xml" === a || !a && n.indexOf("xml") >= 0,
                              i = s ? t.responseXML : t.responseText;
                          return s && "parsererror" === i.documentElement.nodeName && e.error && e.error("parsererror"), r && r.dataFilter && (i = r.dataFilter(i, a)), "string" == typeof i && ("json" === a || !a && n.indexOf("json") >= 0 ? i = F(i) : ("script" === a || !a && n.indexOf("javascript") >= 0) && e.globalEval(i)), i
                      }
              }
          }
      }
      if (!this.length) return r("ajaxSubmit: skipping submit process - no element selected"), this;
      var s, i, o, l = this;
      "function" == typeof t && (t = {
          success: t
      }), s = this.attr("method"), (o = (o = "string" == typeof(i = this.attr("action")) ? e.trim(i) : "") || window.location.href || "") && (o = (o.match(/^([^#]+)/) || [])[1]), t = e.extend(!0, {
          url: o,
          success: e.ajaxSettings.success,
          type: s || "GET",
          iframeSrc: /^https/i.test(window.location.href || "") ? "javascript:false" : "about:blank"
      }, t);
      var u = {};
      if (this.trigger("form-pre-serialize", [this, t, u]), u.veto) return r("ajaxSubmit: submit vetoed via form-pre-serialize trigger"), this;
      if (t.beforeSerialize && !1 === t.beforeSerialize(this, t)) return r("ajaxSubmit: submit aborted via beforeSerialize callback"), this;
      var c = t.traditional;
      void 0 === c && (c = e.ajaxSettings.traditional);
      var h, f = [],
          d = this.formToArray(t.semantic, f);
      if (t.data && (t.extraData = t.data, h = e.param(t.data, c)), t.beforeSubmit && !1 === t.beforeSubmit(d, this, t)) return r("ajaxSubmit: submit aborted via beforeSubmit callback"), this;
      if (this.trigger("form-submit-validate", [d, this, t, u]), u.veto) return r("ajaxSubmit: submit vetoed via form-submit-validate trigger"), this;
      var p = e.param(d, c);
      h && (p = p ? p + "&" + h : h), "GET" == t.type.toUpperCase() ? (t.url += (t.url.indexOf("?") >= 0 ? "&" : "?") + p, t.data = null) : t.data = p;
      var m = [];
      if (t.resetForm && m.push(function() {
              l.resetForm()
          }), t.clearForm && m.push(function() {
              l.clearForm(t.includeHidden)
          }), !t.dataType && t.target) {
          var g = t.success || function() {};
          m.push(function(a) {
              var r = t.replaceTarget ? "replaceWith" : "html";
              e(t.target)[r](a).each(g, arguments)
          })
      } else t.success && m.push(t.success);
      t.success = function(e, a, r) {
          for (var n = t.context || t, s = 0, i = m.length; s < i; s++) m[s].apply(n, [e, a, r || l, l])
      };
      var v = e("input:file:enabled[value]", this).length > 0,
          y = "multipart/form-data",
          x = l.attr("enctype") == y || l.attr("encoding") == y,
          b = n.fileapi && n.formdata;
      r("fileAPI :" + b);
      var k = (v || x) && !b;
      !1 !== t.iframe && (t.iframe || k) ? t.closeKeepAlive ? e.get(t.closeKeepAlive, function() {
          a(d)
      }) : a(d) : (v || x) && b ? function(a) {
          for (var r = new FormData, n = 0; n < a.length; n++) r.append(a[n].name, a[n].value);
          if (t.extraData)
              for (var s in t.extraData) t.extraData.hasOwnProperty(s) && r.append(s, t.extraData[s]);
          t.data = null;
          var i = e.extend(!0, {}, e.ajaxSettings, t, {
              contentType: !1,
              processData: !1,
              cache: !1,
              type: "POST"
          });
          t.uploadProgress && (i.xhr = function() {
              var e = jQuery.ajaxSettings.xhr();
              return e.upload && (e.upload.onprogress = function(e) {
                  var a = 0,
                      r = e.loaded || e.position,
                      n = e.total;
                  e.lengthComputable && (a = Math.ceil(r / n * 100)), t.uploadProgress(e, r, n, a)
              }), e
          }), i.data = null;
          var o = i.beforeSend;
          i.beforeSend = function(e, a) {
              a.data = r, o && o.call(a, e, t)
          }, e.ajax(i)
      }(d) : e.ajax(t);
      for (var _ = 0; _ < f.length; _++) f[_] = null;
      return this.trigger("form-submit-notify", [this, t]), this
  }, e.fn.ajaxForm = function(n) {
      if (n = n || {}, n.delegation = n.delegation && e.isFunction(e.fn.on), !n.delegation && 0 === this.length) {
          var s = {
              s: this.selector,
              c: this.context
          };
          return !e.isReady && s.s ? (r("DOM not ready, queuing ajaxForm"), e(function() {
              e(s.s, s.c).ajaxForm(n)
          }), this) : (r("terminating; zero elements found by selector" + (e.isReady ? "" : " (DOM not ready)")), this)
      }
      return n.delegation ? (e(document).off("submit.form-plugin", this.selector, t).off("click.form-plugin", this.selector, a).on("submit.form-plugin", this.selector, n, t).on("click.form-plugin", this.selector, n, a), this) : this.ajaxFormUnbind().bind("submit.form-plugin", n, t).bind("click.form-plugin", n, a)
  }, e.fn.ajaxFormUnbind = function() {
      return this.unbind("submit.form-plugin click.form-plugin")
  }, e.fn.formToArray = function(t, a) {
      var r = [];
      if (0 === this.length) return r;
      var s = this[0],
          i = t ? s.getElementsByTagName("*") : s.elements;
      if (!i) return r;
      var o, l, u, c, h, f, d;
      for (o = 0, f = i.length; o < f; o++)
          if (h = i[o], u = h.name)
              if (t && s.clk && "image" == h.type) h.disabled || s.clk != h || (r.push({
                  name: u,
                  value: e(h).val(),
                  type: h.type
              }), r.push({
                  name: u + ".x",
                  value: s.clk_x
              }, {
                  name: u + ".y",
                  value: s.clk_y
              }));
              else if ((c = e.fieldValue(h, !0)) && c.constructor == Array)
          for (a && a.push(h), l = 0, d = c.length; l < d; l++) r.push({
              name: u,
              value: c[l]
          });
      else if (n.fileapi && "file" == h.type && !h.disabled) {
          a && a.push(h);
          var p = h.files;
          if (p.length)
              for (l = 0; l < p.length; l++) r.push({
                  name: u,
                  value: p[l],
                  type: h.type
              });
          else r.push({
              name: u,
              value: "",
              type: h.type
          })
      } else null !== c && void 0 !== c && (a && a.push(h), r.push({
          name: u,
          value: c,
          type: h.type,
          required: h.required
      }));
      if (!t && s.clk) {
          var m = e(s.clk),
              g = m[0];
          (u = g.name) && !g.disabled && "image" == g.type && (r.push({
              name: u,
              value: m.val()
          }), r.push({
              name: u + ".x",
              value: s.clk_x
          }, {
              name: u + ".y",
              value: s.clk_y
          }))
      }
      return r
  }, e.fn.formSerialize = function(t) {
      return e.param(this.formToArray(t))
  }, e.fn.fieldSerialize = function(t) {
      var a = [];
      return this.each(function() {
          var r = this.name;
          if (r) {
              var n = e.fieldValue(this, t);
              if (n && n.constructor == Array)
                  for (var s = 0, i = n.length; s < i; s++) a.push({
                      name: r,
                      value: n[s]
                  });
              else null !== n && void 0 !== n && a.push({
                  name: this.name,
                  value: n
              })
          }
      }), e.param(a)
  }, e.fn.fieldValue = function(t) {
      for (var a = [], r = 0, n = this.length; r < n; r++) {
          var s = this[r],
              i = e.fieldValue(s, t);
          null === i || void 0 === i || i.constructor == Array && !i.length || (i.constructor == Array ? e.merge(a, i) : a.push(i))
      }
      return a
  }, e.fieldValue = function(t, a) {
      var r = t.name,
          n = t.type,
          s = t.tagName.toLowerCase();
      if (void 0 === a && (a = !0), a && (!r || t.disabled || "reset" == n || "button" == n || ("checkbox" == n || "radio" == n) && !t.checked || ("submit" == n || "image" == n) && t.form && t.form.clk != t || "select" == s && -1 == t.selectedIndex)) return null;
      if ("select" == s) {
          var i = t.selectedIndex;
          if (i < 0) return null;
          for (var o = [], l = t.options, u = "select-one" == n, c = u ? i + 1 : l.length, h = u ? i : 0; h < c; h++) {
              var f = l[h];
              if (f.selected) {
                  var d = f.value;
                  if (d || (d = f.attributes && f.attributes.value && !f.attributes.value.specified ? f.text : f.value), u) return d;
                  o.push(d)
              }
          }
          return o
      }
      return e(t).val()
  }, e.fn.clearForm = function(t) {
      return this.each(function() {
          e("input,select,textarea", this).clearFields(t)
      })
  }, e.fn.clearFields = e.fn.clearInputs = function(t) {
      var a = /^(?:color|date|datetime|email|month|number|password|range|search|tel|text|time|url|week)$/i;
      return this.each(function() {
          var r = this.type,
              n = this.tagName.toLowerCase();
          a.test(r) || "textarea" == n ? this.value = "" : "checkbox" == r || "radio" == r ? this.checked = !1 : "select" == n ? this.selectedIndex = -1 : t && (!0 === t && /hidden/.test(r) || "string" == typeof t && e(this).is(t)) && (this.value = "")
      })
  }, e.fn.resetForm = function() {
      return this.each(function() {
          ("function" == typeof this.reset || "object" == typeof this.reset && !this.reset.nodeType) && this.reset()
      })
  }, e.fn.enable = function(e) {
      return void 0 === e && (e = !0), this.each(function() {
          this.disabled = !e
      })
  }, e.fn.selected = function(t) {
      return void 0 === t && (t = !0), this.each(function() {
          var a = this.type;
          if ("checkbox" == a || "radio" == a) this.checked = t;
          else if ("option" == this.tagName.toLowerCase()) {
              var r = e(this).parent("select");
              t && r[0] && "select-one" == r[0].type && r.find("option").selected(!1), this.selected = t
          }
      })
  }, e.fn.ajaxSubmit.debug = !1
}(jQuery),
function(e) {
  function t(t) {
      var a = this;
      (t = e.event.fix(t || window.e)).type = "paste", setTimeout(function() {
          e.event.handle.call(a, t)
      }, 1)
  }
  var a = void 0 != window.orientation,
      r = e.browser.opera || e.browser.mozilla && parseFloat(e.browser.version.substr(0, 3)) < 1.9 ? "input" : "paste";
  e.event.special.paste = {
      setup: function() {
          this.addEventListener ? this.addEventListener(r, t, !1) : this.attachEvent && this.attachEvent(r, t)
      },
      teardown: function() {
          this.removeEventListener ? this.removeEventListener(r, t, !1) : this.detachEvent && this.detachEvent(r, t)
      }
  }, e.extend({
      mask: {
          rules: {
              z: /[a-z]/,
              Z: /[A-Z]/,
              a: /[a-zA-Z]/,
              "*": /[0-9a-zA-Z]/,
              "@": /[0-9a-zA-ZçÇáàãâéèêíìóòôõúùü]/
          },
          keyRepresentation: {
              8: "backspace",
              9: "tab",
              13: "enter",
              16: "shift",
              17: "control",
              18: "alt",
              27: "esc",
              33: "page up",
              34: "page down",
              35: "end",
              36: "home",
              37: "left",
              38: "up",
              39: "right",
              40: "down",
              45: "insert",
              46: "delete",
              116: "f5",
              123: "f12",
              224: "command"
          },
          iphoneKeyRepresentation: {
              10: "go",
              127: "delete"
          },
          signals: {
              "+": "",
              "-": "-"
          },
          options: {
              attr: "alt",
              mask: null,
              type: "fixed",
              maxLength: -1,
              defaultValue: "",
              signal: !1,
              textAlign: !0,
              selectCharsOnFocus: !0,
              autoTab: !0,
              setSize: !1,
              fixedChars: "[(),.:/ -]",
              onInvalid: function() {},
              onValid: function() {},
              onOverflow: function() {}
          },
          masks: {
              phone: {
                  mask: "(99) 9999-9999"
              },
              "phone-us": {
                  mask: "(999) 999-9999"
              },
              cpf: {
                  mask: "999.999.999-99"
              },
              cnpj: {
                  mask: "99.999.999/9999-99"
              },
              date: {
                  mask: "39/19/9999"
              },
              "date-us": {
                  mask: "19/39/9999"
              },
              cep: {
                  mask: "99999-999"
              },
              time: {
                  mask: "29:59"
              },
              cc: {
                  mask: "9999 9999 9999 9999"
              },
              integer: {
                  mask: "999.999.999.999",
                  type: "reverse"
              },
              decimal: {
                  mask: "99,999.999.999.999",
                  type: "reverse",
                  defaultValue: "000"
              },
              "decimal-us": {
                  mask: "99.999,999,999,999",
                  type: "reverse",
                  defaultValue: "000"
              },
              "signed-decimal": {
                  mask: "99,999.999.999.999",
                  type: "reverse",
                  defaultValue: "+000"
              },
              "signed-decimal-us": {
                  mask: "99,999.999.999.999",
                  type: "reverse",
                  defaultValue: "+000"
              }
          },
          init: function() {
              if (!this.hasInit) {
                  var t, r = this,
                      n = a ? this.iphoneKeyRepresentation : this.keyRepresentation;
                  for (this.ignore = !1, t = 0; t <= 9; t++) this.rules[t] = new RegExp("[0-" + t + "]");
                  this.keyRep = n, this.ignoreKeys = [], e.each(n, function(e) {
                      r.ignoreKeys.push(parseInt(e))
                  }), this.hasInit = !0
              }
          },
          set: function(t, a) {
              var r = this,
                  n = e(t),
                  s = "maxLength";
              return a = a || {}, this.init(), n.each(function() {
                  a.attr && (r.options.attr = a.attr);
                  var t = e(this),
                      n = e.extend({}, r.options),
                      i = t.attr(n.attr),
                      o = "";
                  if ((o = "string" == typeof a ? a : "" != i ? i : null) && (n.mask = o), r.masks[o] && (n = e.extend(n, r.masks[o])), "object" == typeof a && a.constructor != Array && (n = e.extend(n, a)), e.metadata && (n = e.extend(n, t.metadata())), null != n.mask) {
                      t.data("mask") && r.unset(t);
                      var l = n.defaultValue,
                          u = "reverse" == n.type,
                          c = new RegExp(n.fixedChars, "g");
                      if (-1 == n.maxLength && (n.maxLength = t.attr(s)), "fixed" != (n = e.extend({}, n, {
                              fixedCharsReg: new RegExp(n.fixedChars),
                              fixedCharsRegG: c,
                              maskArray: n.mask.split(""),
                              maskNonFixedCharsArray: n.mask.replace(c, "").split("")
                          })).type && !u || !n.setSize || t.attr("size") || t.attr("size", n.mask.length), u && n.textAlign && t.css("text-align", "right"), "" != this.value || "" != l) {
                          var h = r.string("" != this.value ? this.value : l, n);
                          this.defaultValue = h, t.val(h)
                      }
                      "infinite" == n.type && (n.type = "repeat"), t.data("mask", n), t.removeAttr(s), t.bind("keydown.mask", {
                          func: r._onKeyDown,
                          thisObj: r
                      }, r._onMask).bind("keypress.mask", {
                          func: r._onKeyPress,
                          thisObj: r
                      }, r._onMask).bind("keyup.mask", {
                          func: r._onKeyUp,
                          thisObj: r
                      }, r._onMask).bind("paste.mask", {
                          func: r._onPaste,
                          thisObj: r
                      }, r._onMask).bind("focus.mask", r._onFocus).bind("blur.mask", r._onBlur).bind("change.mask", r._onChange)
                  }
              })
          },
          unset: function(t) {
              return e(t).each(function() {
                  var t = e(this);
                  if (t.data("mask")) {
                      var a = t.data("mask").maxLength; - 1 != a && t.attr("maxLength", a), t.unbind(".mask").removeData("mask")
                  }
              })
          },
          string: function(t, a) {
              this.init();
              var r = {};
              switch ("string" != typeof t && (t = String(t)), typeof a) {
                  case "string":
                      this.masks[a] ? r = e.extend(r, this.masks[a]) : r.mask = a;
                      break;
                  case "object":
                      r = a
              }
              r.fixedChars || (r.fixedChars = this.options.fixedChars);
              var n = new RegExp(r.fixedChars),
                  s = new RegExp(r.fixedChars, "g");
              if ("reverse" == r.type && r.defaultValue && void 0 !== this.signals[r.defaultValue.charAt(0)]) {
                  var i = t.charAt(0);
                  r.signal = void 0 !== this.signals[i] ? this.signals[i] : this.signals[r.defaultValue.charAt(0)], r.defaultValue = r.defaultValue.substring(1)
              }
              return this.__maskArray(t.split(""), r.mask.replace(s, "").split(""), r.mask.split(""), r.type, r.maxLength, r.defaultValue, n, r.signal)
          },
          _onFocus: function(t) {
              var a = e(this),
                  r = a.data("mask");
              r.inputFocusValue = a.val(), r.changed = !1, r.selectCharsOnFocus && a.select()
          },
          _onBlur: function(t) {
              var a = e(this),
                  r = a.data("mask");
              r.inputFocusValue == a.val() || r.changed || a.trigger("change")
          },
          _onChange: function(t) {
              e(this).data("mask").changed = !0
          },
          _onMask: function(t) {
              var a = t.data.thisObj,
                  r = {};
              return r._this = t.target, r.$this = e(r._this), !!r.$this.attr("readonly") || (r.data = r.$this.data("mask"), r[r.data.type] = !0, r.value = r.$this.val(), r.nKey = a.__getKeyNumber(t), r.range = a.__getRange(r._this), r.valueArray = r.value.split(""), t.data.func.call(a, t, r))
          },
          _onKeyDown: function(t, r) {
              if (this.ignore = e.inArray(r.nKey, this.ignoreKeys) > -1 || t.ctrlKey || t.metaKey || t.altKey, this.ignore) {
                  var n = this.keyRep[r.nKey];
                  r.data.onValid.call(r._this, n || "", r.nKey)
              }
              return !a || this._keyPress(t, r)
          },
          _onKeyUp: function(e, t) {
              return 9 == t.nKey || 16 == t.nKey || ("repeat" == t.data.type ? (this.__autoTab(t), !0) : this._onPaste(e, t))
          },
          _onPaste: function(t, a) {
              a.reverse && this.__changeSignal(t.type, a);
              var r = this.__maskArray(a.valueArray, a.data.maskNonFixedCharsArray, a.data.maskArray, a.data.type, a.data.maxLength, a.data.defaultValue, a.data.fixedCharsReg, a.data.signal);
              return a.$this.val(r), !a.reverse && a.data.defaultValue.length && a.range.start == a.range.end && this.__setRange(a._this, a.range.start, a.range.end), !e.browser.msie && !e.browser.safari || a.reverse || this.__setRange(a._this, a.range.start, a.range.end), !!this.ignore || (this.__autoTab(a), !0)
          },
          _onKeyPress: function(e, t) {
              if (this.ignore) return !0;
              t.reverse && this.__changeSignal(e.type, t);
              var a = String.fromCharCode(t.nKey),
                  r = t.range.start,
                  n = t.value,
                  s = t.data.maskArray;
              t.reverse && (n = n.substr(0, r) + a + n.substr(t.range.end, n.length), t.data.signal && r - t.data.signal.length > 0 && (r -= t.data.signal.length));
              var i = n.replace(t.data.fixedCharsRegG, "").split(""),
                  o = this.__extraPositionsTill(r, s, t.data.fixedCharsReg);
              if (t.rsEp = r + o, t.repeat && (t.rsEp = 0), !this.rules[s[t.rsEp]] || -1 != t.data.maxLength && i.length >= t.data.maxLength && t.repeat) return t.data.onOverflow.call(t._this, a, t.nKey), !1;
              if (!this.rules[s[t.rsEp]].test(a)) return t.data.onInvalid.call(t._this, a, t.nKey), !1;
              t.data.onValid.call(t._this, a, t.nKey);
              var l = this.__maskArray(i, t.data.maskNonFixedCharsArray, s, t.data.type, t.data.maxLength, t.data.defaultValue, t.data.fixedCharsReg, t.data.signal, o);
              return t.$this.val(l), t.reverse ? this._keyPressReverse(e, t) : !t.fixed || this._keyPressFixed(e, t)
          },
          _keyPressFixed: function(e, t) {
              return t.range.start == t.range.end ? (0 == t.rsEp && 0 == t.value.length || t.rsEp < t.value.length) && this.__setRange(t._this, t.rsEp, t.rsEp + 1) : this.__setRange(t._this, t.range.start, t.range.end), !0
          },
          _keyPressReverse: function(t, a) {
              return e.browser.msie && (0 == a.range.start && 0 == a.range.end || a.range.start != a.range.end) && this.__setRange(a._this, a.value.length), !1
          },
          __autoTab: function(e) {
              if (e.data.autoTab && (e.$this.val().length >= e.data.maskArray.length && !e.repeat || -1 != e.data.maxLength && e.valueArray.length >= e.data.maxLength && e.repeat)) {
                  var t = this.__getNextInput(e._this, e.data.autoTab);
                  t && (e.$this.trigger("blur"), t.focus().select())
              }
          },
          __changeSignal: function(e, t) {
              if (!1 !== t.data.signal) {
                  var a = "paste" == e ? t.value.charAt(0) : String.fromCharCode(t.nKey);
                  this.signals && void 0 !== this.signals[a] && (t.data.signal = this.signals[a])
              }
          },
          __getKeyNumber: function(e) {
              return e.charCode || e.keyCode || e.which
          },
          __maskArray: function(e, t, a, r, n, s, i, o, l) {
              switch ("reverse" == r && e.reverse(), e = this.__removeInvalidChars(e, t, "repeat" == r || "infinite" == r), s && (e = this.__applyDefaultValue.call(e, s)), e = this.__applyMask(e, a, l, i), r) {
                  case "reverse":
                      return e.reverse(), (o || "") + e.join("").substring(e.length - a.length);
                  case "infinite":
                  case "repeat":
                      var u = e.join("");
                      return -1 != n && e.length >= n ? u.substring(0, n) : u;
                  default:
                      return e.join("").substring(0, a.length)
              }
              return ""
          },
          __applyDefaultValue: function(e) {
              var t, a = e.length;
              for (t = this.length - 1; t >= 0 && this[t] == e.charAt(0); t--) this.pop();
              for (t = 0; t < a; t++) this[t] || (this[t] = e.charAt(t));
              return this
          },
          __removeInvalidChars: function(e, t, a) {
              for (var r = 0, n = 0; r < e.length; r++) t[n] && this.rules[t[n]] && !this.rules[t[n]].test(e[r]) && (e.splice(r, 1), a || n--, r--), a || n++;
              return e
          },
          __applyMask: function(e, t, a, r) {
              void 0 === a && (a = 0);
              for (var n = 0; n < e.length + a; n++) t[n] && r.test(t[n]) && e.splice(n, 0, t[n]);
              return e
          },
          __extraPositionsTill: function(e, t, a) {
              for (var r = 0; a.test(t[e++]);) r++;
              return r
          },
          __getNextInput: function(t, a) {
              var r, n = t.form.elements,
                  s = null;
              for (r = e.inArray(t, n) + 1; r < n.length; r++)
                  if (s = e(n[r]), this.__isNextInput(s, a)) return s;
              var i, o = document.forms,
                  l = null;
              for (i = e.inArray(t.form, o) + 1; i < o.length; i++)
                  for (l = o[i].elements, r = 0; r < l.length; r++)
                      if (s = e(l[r]), this.__isNextInput(s, a)) return s;
              return null
          },
          __isNextInput: function(e, t) {
              var a = e.get(0);
              return a && (a.offsetWidth > 0 || a.offsetHeight > 0) && "FIELDSET" != a.nodeName && (!0 === t || "string" == typeof t && e.is(t))
          },
          __setRange: function(e, t, a) {
              if (void 0 === a && (a = t), e.setSelectionRange) e.setSelectionRange(t, a);
              else {
                  var r = e.createTextRange();
                  r.collapse(), r.moveStart("character", t), r.moveEnd("character", a - t), r.select()
              }
          },
          __getRange: function(t) {
              if (!e.browser.msie) return {
                  start: t.selectionStart,
                  end: t.selectionEnd
              };
              var a = {
                      start: 0,
                      end: 0
                  },
                  r = document.selection.createRange();
              return a.start = 0 - r.duplicate().moveStart("character", -1e5), a.end = a.start + r.text.length, a
          },
          unmaskedVal: function(t) {
              return e(t).val().replace(e.mask.fixedCharsRegG, "")
          }
      }
  }), e.fn.extend({
      setMask: function(t) {
          return e.mask.set(this, t)
      },
      unsetMask: function() {
          return e.mask.unset(this)
      },
      unmaskedVal: function() {
          return e.mask.unmaskedVal(this[0])
      }
  })
}(jQuery),
function(e, t, a, r) {
  e.fn.quicksearch = function(a, r) {
      var n, s, i, o, l = "",
          u = this,
          c = e.extend({
              delay: 100,
              selector: null,
              stripeRows: null,
              loader: null,
              noResults: "",
              bind: "keyup",
              onBefore: function() {},
              onAfter: function() {},
              show: function() {
                  this.style.display = ""
              },
              hide: function() {
                  this.style.display = "none"
              },
              prepareQuery: function(e) {
                  return e.toLowerCase().split(" ")
              },
              testQuery: function(e, t, a) {
                  for (var r = 0; r < e.length; r += 1)
                      if (-1 === t.indexOf(e[r])) return !1;
                  return !0
              }
          }, r);
      return this.go = function() {
          for (var e = 0, t = !0, a = c.prepareQuery(l), r = 0 === l.replace(" ", "").length, e = 0, n = i.length; e < n; e++) r || c.testQuery(a, s[e], i[e]) ? (c.show.apply(i[e]), t = !1) : c.hide.apply(i[e]);
          return t ? this.results(!1) : (this.results(!0), this.stripe()), this.loader(!1), c.onAfter(), this
      }, this.stripe = function() {
          if ("object" == typeof c.stripeRows && null !== c.stripeRows) {
              var t = c.stripeRows.join(" "),
                  a = c.stripeRows.length;
              o.not(":hidden").each(function(r) {
                  e(this).removeClass(t).addClass(c.stripeRows[r % a])
              })
          }
          return this
      }, this.strip_html = function(t) {
          var a = t.replace(new RegExp("<[^<]+>", "g"), "");
          return a = e.trim(a.toLowerCase())
      }, this.results = function(t) {
          return "string" == typeof c.noResults && "" !== c.noResults && (t ? e(c.noResults).hide() : e(c.noResults).show()), this
      }, this.loader = function(t) {
          return "string" == typeof c.loader && "" !== c.loader && (t ? e(c.loader).show() : e(c.loader).hide()), this
      }, this.cache = function() {
          o = e(a), "string" == typeof c.noResults && "" !== c.noResults && (o = o.not(c.noResults));
          var t = "string" == typeof c.selector ? o.find(c.selector) : e(a).not(c.noResults);
          return s = t.map(function() {
              return u.strip_html(this.innerHTML)
          }), i = o.map(function() {
              return this
          }), this.go()
      }, this.trigger = function() {
          return this.loader(!0), c.onBefore(), t.clearTimeout(n), n = t.setTimeout(function() {
              u.go()
          }, c.delay), this
      }, this.cache(), this.results(!0), this.stripe(), this.loader(!1), this.each(function() {
          e(this).bind(c.bind, function() {
              l = e(this).val(), u.trigger()
          })
      })
  }
}(jQuery, this, document);