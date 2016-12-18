(function($, win, doc) {

    var html = doc.documentElement,
        head = doc.head,
        body = doc.body,
        replace = 'replace',

        attributes = 'attributes',
        classes = 'classes',
        events = 'events',
        hooks = 'hooks',

        timer_reset = clearTimeout,
        timer_set = setTimeout;

    $.format = function(s, a) {
        return s[replace](/%(\{\d+\}|\d+)/g, function(b, c) {
            c = +c - 1;
            return typeof a[c] !== "undefined" ? a[c] : b;
        });
    }

    function count(x) {
        return x.length;
    }

    function pattern(a, b) {
        return new RegExp(a, b);
    }

    function to_lower_case(s) {
        return s.toLowerCase();
    }

    function to_upper_case(s) {
        return s.toUpperCase();
    }

    function do_table_data() {
        var $delete = $('.button-destruct').css('display', 'none'),
            $rows = $('.table-data');
        if (!count($rows)) return;
        $rows[events].capture("touchstart mousedown", 'tbody tr', function() {
            $(this)[classes].toggle('on');
            $delete.css('display', count($(this).parent().has('.on')) ? "" : 'none');
            return false;
        });
    }

    function do_tree() {
        var $menus = $('aside.panel-span nav li:not(.current) a + ul');
        if (!count($menus)) return;
        $menus.previous().click(function() {
            return $(this).parent()[classes].toggle('on'), false;
        });
    }

    function do_toggle() {
        var $toggles = $('.input--checkbox,.input--radio'),
            y = 'toggle i i-toggle i-toggle--%1%2';
        if (!count($toggles)) return;
        $toggles.is(function() {
            return to_lower_case($(this).parent().get('node-name')) === 'label';
        }).each(function() {
            if ($(this).data.get('mecha')) return;
            var toggle = $(this).get('type') === 'checkbox' ? 1 : 0,
                h = ($(this).get('disable') ? ' x' : "") + ($(this).get('check') ? ' on' : "");
            $(this).data.set('mecha', 1).change(function() {
                $(this).previous()[attributes].set('class', $.format(y, [$(this).get('type'), $(this).get('check') ? ' on' : ""]));
                var self = this,
                    self_type = $(self).get('type'),
                    self_name = $(self).get('name');
                if (self_type === 'radio') {
                    $toggles.is(function() {
                        return this !== self && $(this).get('name') === self_name && !$(this).get('disable') && !$(this).get('read-only');
                    }).previous()[attributes].set('class', $.format(y, [self_type, ""]));
                }
            }).before($('<a class="' + $.format(y, [$(this).get('type'), h]) + '" href=""></a>').click(function() {
                $(this).next(function() {
                    return !$(this).get('disable') && !$(this).get('read-only');
                }).set('check', toggle ? !$(this)[classes].get('on') : true).change();
                return false;
            }));
        }).parent()[events].set("touchstart mousedown", false);
    }

    function do_tab() {
        var $tabs = $('.tab-button'), $id;
        if (!count($tabs)) return;
        $tabs.click(function() {
            $id = (this.hash || "")[replace]('#', "");
            $id = ($id && $('#' + $id)) || $(this).closest('.tab').find('.tab-content').index($(this).index());
            $(this)[classes].set('on').kin('.tab-button')[classes].reset('on');
            if ($id) {
                $id[classes][$(this)[classes].get('toggle') ? 'toggle' : 'set']('on').kin('.tab-content')[classes].reset('on');
            }
            return false;
        })[events].reset("touchstart mousedown", false).is('.on').click();
    }

    $.slug = function(s, l, h, x) {
        h = h || '\\-';
        var H = h[replace](/\\/g, "");
        var a = {
            '¹': '1',
            '²': '2',
            '³': '3',
            '°': '0',
            'æ': 'ae',
            'ǽ': 'ae',
            'À': 'A',
            'Á': 'A',
            'Â': 'A',
            'Ã': 'A',
            'Å': 'A',
            'Ǻ': 'A',
            'Ă': 'A',
            'Ǎ': 'A',
            'Æ': 'AE',
            'Ǽ': 'AE',
            'à': 'a',
            'á': 'a',
            'â': 'a',
            'ã': 'a',
            'å': 'a',
            'ǻ': 'a',
            'ă': 'a',
            'ǎ': 'a',
            'ª': 'a',
            '@': 'at',
            'Ĉ': 'C',
            'Ċ': 'C',
            'ĉ': 'c',
            'ċ': 'c',
            '©': 'c',
            'Ð': 'Dj',
            'Đ': 'D',
            'ð': 'dj',
            'đ': 'd',
            'È': 'E',
            'É': 'E',
            'Ê': 'E',
            'Ë': 'E',
            'Ĕ': 'E',
            'Ė': 'E',
            'è': 'e',
            'é': 'e',
            'ê': 'e',
            'ë': 'e',
            'ĕ': 'e',
            'ė': 'e',
            'ƒ': 'f',
            'Ĝ': 'G',
            'Ġ': 'G',
            'ĝ': 'g',
            'ġ': 'g',
            'Ĥ': 'H',
            'Ħ': 'H',
            'ĥ': 'h',
            'ħ': 'h',
            'Ì': 'I',
            'Í': 'I',
            'Î': 'I',
            'Ï': 'I',
            'Ĩ': 'I',
            'Ĭ': 'I',
            'Ǐ': 'I',
            'Į': 'I',
            'Ĳ': 'IJ',
            'ì': 'i',
            'í': 'i',
            'î': 'i',
            'ï': 'i',
            'ĩ': 'i',
            'ĭ': 'i',
            'ǐ': 'i',
            'į': 'i',
            'ĳ': 'ij',
            'Ĵ': 'J',
            'ĵ': 'j',
            'Ĺ': 'L',
            'Ľ': 'L',
            'Ŀ': 'L',
            'ĺ': 'l',
            'ľ': 'l',
            'ŀ': 'l',
            'Ñ': 'N',
            'ñ': 'n',
            'ŉ': 'n',
            'Ò': 'O',
            'Ô': 'O',
            'Õ': 'O',
            'Ō': 'O',
            'Ŏ': 'O',
            'Ǒ': 'O',
            'Ő': 'O',
            'Ơ': 'O',
            'Ø': 'O',
            'Ǿ': 'O',
            'Œ': 'OE',
            'ò': 'o',
            'ô': 'o',
            'õ': 'o',
            'ō': 'o',
            'ŏ': 'o',
            'ǒ': 'o',
            'ő': 'o',
            'ơ': 'o',
            'ø': 'o',
            'ǿ': 'o',
            'º': 'o',
            'œ': 'oe',
            'Ŕ': 'R',
            'Ŗ': 'R',
            'ŕ': 'r',
            'ŗ': 'r',
            'Ŝ': 'S',
            'Ș': 'S',
            'ŝ': 's',
            'ș': 's',
            'ſ': 's',
            'Ţ': 'T',
            'Ț': 'T',
            'Ŧ': 'T',
            'Þ': 'TH',
            'ţ': 't',
            'ț': 't',
            'ŧ': 't',
            'þ': 'th',
            'Ù': 'U',
            'Ú': 'U',
            'Û': 'U',
            'Ũ': 'U',
            'Ŭ': 'U',
            'Ű': 'U',
            'Ų': 'U',
            'Ư': 'U',
            'Ǔ': 'U',
            'Ǖ': 'U',
            'Ǘ': 'U',
            'Ǚ': 'U',
            'Ǜ': 'U',
            'ù': 'u',
            'ú': 'u',
            'û': 'u',
            'ũ': 'u',
            'ŭ': 'u',
            'ű': 'u',
            'ų': 'u',
            'ư': 'u',
            'ǔ': 'u',
            'ǖ': 'u',
            'ǘ': 'u',
            'ǚ': 'u',
            'ǜ': 'u',
            'Ŵ': 'W',
            'ŵ': 'w',
            'Ý': 'Y',
            'Ÿ': 'Y',
            'Ŷ': 'Y',
            'ý': 'y',
            'ÿ': 'y',
            'ŷ': 'y',
            'Ъ': "",
            'Ь': "",
            'А': 'A',
            'Б': 'B',
            'Ц': 'C',
            'Ч': 'Ch',
            'Д': 'D',
            'Е': 'E',
            'Ё': 'E',
            'Э': 'E',
            'Ф': 'F',
            'Г': 'G',
            'Х': 'H',
            'И': 'I',
            'Й': 'J',
            'Я': 'Ja',
            'Ю': 'Ju',
            'К': 'K',
            'Л': 'L',
            'М': 'M',
            'Н': 'N',
            'О': 'O',
            'П': 'P',
            'Р': 'R',
            'С': 'S',
            'Ш': 'Sh',
            'Щ': 'Shch',
            'Т': 'T',
            'У': 'U',
            'В': 'V',
            'Ы': 'Y',
            'З': 'Z',
            'Ж': 'Zh',
            'ъ': "",
            'ь': "",
            'а': 'a',
            'б': 'b',
            'ц': 'c',
            'ч': 'ch',
            'д': 'd',
            'е': 'e',
            'ё': 'e',
            'э': 'e',
            'ф': 'f',
            'г': 'g',
            'х': 'h',
            'и': 'i',
            'й': 'j',
            'я': 'ja',
            'ю': 'ju',
            'к': 'k',
            'л': 'l',
            'м': 'm',
            'н': 'n',
            'о': 'o',
            'п': 'p',
            'р': 'r',
            'с': 's',
            'ш': 'sh',
            'щ': 'shch',
            'т': 't',
            'у': 'u',
            'в': 'v',
            'ы': 'y',
            'з': 'z',
            'ж': 'zh',
            'Ä': 'AE',
            'Ö': 'OE',
            'Ü': 'UE',
            'ß': 'ss',
            'ä': 'ae',
            'ö': 'oe',
            'ü': 'ue',
            'Ç': 'C',
            'Ğ': 'G',
            'İ': 'I',
            'Ş': 'S',
            'ç': 'c',
            'ğ': 'g',
            'ı': 'i',
            'ş': 's',
            'Ā': 'A',
            'Ē': 'E',
            'Ģ': 'G',
            'Ī': 'I',
            'Ķ': 'K',
            'Ļ': 'L',
            'Ņ': 'N',
            'Ū': 'U',
            'ā': 'a',
            'ē': 'e',
            'ģ': 'g',
            'ī': 'i',
            'ķ': 'k',
            'ļ': 'l',
            'ņ': 'n',
            'ū': 'u',
            'Ґ': 'G',
            'І': 'I',
            'Ї': 'Ji',
            'Є': 'Ye',
            'ґ': 'g',
            'і': 'i',
            'ї': 'ji',
            'є': 'ye',
            'Č': 'C',
            'Ď': 'D',
            'Ě': 'E',
            'Ň': 'N',
            'Ř': 'R',
            'Š': 'S',
            'Ť': 'T',
            'Ů': 'U',
            'Ž': 'Z',
            'č': 'c',
            'ď': 'd',
            'ě': 'e',
            'ň': 'n',
            'ř': 'r',
            'š': 's',
            'ť': 't',
            'ů': 'u',
            'ž': 'z',
            'Ą': 'A',
            'Ć': 'C',
            'Ę': 'E',
            'Ł': 'L',
            'Ń': 'N',
            'Ó': 'O',
            'Ś': 'S',
            'Ź': 'Z',
            'Ż': 'Z',
            'ą': 'a',
            'ć': 'c',
            'ę': 'e',
            'ł': 'l',
            'ń': 'n',
            'ó': 'o',
            'ś': 's',
            'ź': 'z',
            'ż': 'z',
            'Α': 'A',
            'Β': 'B',
            'Γ': 'G',
            'Δ': 'D',
            'Ε': 'E',
            'Ζ': 'Z',
            'Η': 'E',
            'Θ': 'Th',
            'Ι': 'I',
            'Κ': 'K',
            'Λ': 'L',
            'Μ': 'M',
            'Ν': 'N',
            'Ξ': 'X',
            'Ο': 'O',
            'Π': 'P',
            'Ρ': 'R',
            'Σ': 'S',
            'Τ': 'T',
            'Υ': 'Y',
            'Φ': 'Ph',
            'Χ': 'Ch',
            'Ψ': 'Ps',
            'Ω': 'O',
            'Ϊ': 'I',
            'Ϋ': 'Y',
            'ά': 'a',
            'έ': 'e',
            'ή': 'e',
            'ί': 'i',
            'ΰ': 'Y',
            'α': 'a',
            'β': 'b',
            'γ': 'g',
            'δ': 'd',
            'ε': 'e',
            'ζ': 'z',
            'η': 'e',
            'θ': 'th',
            'ι': 'i',
            'κ': 'k',
            'λ': 'l',
            'μ': 'm',
            'ν': 'n',
            'ξ': 'x',
            'ο': 'o',
            'π': 'p',
            'ρ': 'r',
            'ς': 's',
            'σ': 's',
            'τ': 't',
            'υ': 'y',
            'φ': 'ph',
            'χ': 'ch',
            'ψ': 'ps',
            'ω': 'o',
            'ϊ': 'i',
            'ϋ': 'y',
            'ό': 'o',
            'ύ': 'y',
            'ώ': 'o',
            'ϐ': 'b',
            'ϑ': 'th',
            'ϒ': 'Y',
            'أ': 'a',
            'ب': 'b',
            'ت': 't',
            'ث': 'th',
            'ج': 'g',
            'ح': 'h',
            'خ': 'kh',
            'د': 'd',
            'ذ': 'th',
            'ر': 'r',
            'ز': 'z',
            'س': 's',
            'ش': 'sh',
            'ص': 's',
            'ض': 'd',
            'ط': 't',
            'ظ': 'th',
            'ع': 'aa',
            'غ': 'gh',
            'ف': 'f',
            'ق': 'k',
            'ك': 'k',
            'ل': 'l',
            'م': 'm',
            'ن': 'n',
            'ه': 'h',
            'و': 'o',
            'ي': 'y',
            'ạ': 'a',
            'ả': 'a',
            'ầ': 'a',
            'ấ': 'a',
            'ậ': 'a',
            'ẩ': 'a',
            'ẫ': 'a',
            'ằ': 'a',
            'ắ': 'a',
            'ặ': 'a',
            'ẳ': 'a',
            'ẵ': 'a',
            'ẹ': 'e',
            'ẻ': 'e',
            'ẽ': 'e',
            'ề': 'e',
            'ế': 'e',
            'ệ': 'e',
            'ể': 'e',
            'ễ': 'e',
            'ị': 'i',
            'ỉ': 'i',
            'ọ': 'o',
            'ỏ': 'o',
            'ồ': 'o',
            'ố': 'o',
            'ộ': 'o',
            'ổ': 'o',
            'ỗ': 'o',
            'ờ': 'o',
            'ớ': 'o',
            'ợ': 'o',
            'ở': 'o',
            'ỡ': 'o',
            'ụ': 'u',
            'ủ': 'u',
            'ừ': 'u',
            'ứ': 'u',
            'ự': 'u',
            'ử': 'u',
            'ữ': 'u',
            'ỳ': 'y',
            'ỵ': 'y',
            'ỷ': 'y',
            'ỹ': 'y',
            'Ạ': 'A',
            'Ả': 'A',
            'Ầ': 'A',
            'Ấ': 'A',
            'Ậ': 'A',
            'Ẩ': 'A',
            'Ẫ': 'A',
            'Ằ': 'A',
            'Ắ': 'A',
            'Ặ': 'A',
            'Ẳ': 'A',
            'Ẵ': 'A',
            'Ẹ': 'E',
            'Ẻ': 'E',
            'Ẽ': 'E',
            'Ề': 'E',
            'Ế': 'E',
            'Ệ': 'E',
            'Ể': 'E',
            'Ễ': 'E',
            'Ị': 'I',
            'Ỉ': 'I',
            'Ọ': 'O',
            'Ỏ': 'O',
            'Ồ': 'O',
            'Ố': 'O',
            'Ộ': 'O',
            'Ổ': 'O',
            'Ỗ': 'O',
            'Ờ': 'O',
            'Ớ': 'O',
            'Ợ': 'O',
            'Ở': 'O',
            'Ỡ': 'O',
            'Ụ': 'U',
            'Ủ': 'U',
            'Ừ': 'U',
            'Ứ': 'U',
            'Ự': 'U',
            'Ử': 'U',
            'Ữ': 'U',
            'Ỳ': 'Y',
            'Ỵ': 'Y',
            'Ỷ': 'Y',
            'Ỹ': 'Y'
        }, i;
        for (i in a) s[replace](pattern(i, 'g'), a[i]);
        s = s[replace](/<.*?>|&(?:[a-z\d]+|\#\d+|\#x[a-f\d]+);/gi, H);
        s = s[replace](pattern('[^a-zA-Z\\d' + (x || "") + ']', 'g'), H);
        s = s[replace](pattern('[' + h + ']+', 'g'), H);
        s = s[replace](pattern('^[' + h + ']|[' + h + ']$', 'g'), "");
        return l ? to_lower_case(s) : s;
    }

    var $events = "change keyup cut paste input focus blur";

    function do_slug() {
        var $in = $('[data-slug-i]'),
            $out = $('[data-slug-o]'), $hold;
        if (!count($in) || !count($out)) return;
        $in[events].set($events, function() {
            if (!$hold) {
                $hold = $out.is('[data-slug-o="' + $(this).data.get('slug-i') + '"]');
            }
            if ($hold && !$hold.data.get('x')) {
                var $this = $(this);
                timer_set(function() {
                    $hold.value($.slug($this.value(), 1));
                }, 1);
            }
        });
        $out[events].set($events, function() {
            $(this).data.set('x', !!this.value);
        });
    }

    function do_description() {
        var $in = $('[data-description-i]'),
            $out = $('[data-description-o]'), $hold;
        if (!count($in) || !count($out)) return;
        $in[events].set($events, function() {
            if (!$hold) {
                $hold = $out.is('[data-description-o="' + $(this).data.get('description-i') + '"]');
            }
            if ($hold && !$hold.data.get('x')) {
                var $this = $(this),
                    dots = '\u2026';
                timer_set(function() {
                    var value = $this.value()[replace](/<.*?>|[<>]/g, "").match(/\s*(\S[a-z\d ]*)([^a-z\d ])?\s*/i) || ["", "", ""];
                    value = value[1] + (value[2] && /^[?!.]$/.test(value[2]) ? value[2] : dots);
                    $hold.value(value === dots ? "" : value);
                }, 1);
            }
        });
        $out[events].set($events, function() {
            $(this).data.set('x', !!this.value);
        });
    }

    function do_item() {
        var $item = $('.item.is-can-destruct');
        if (!count($item)) return;
        $item.each(function() {
            $(this).append($('<a class="panel-x" href=""></a>').click(function() {
                return $(this).parent().remove(), false;
            }));
        });
    }

    win.Mecha = $;
    win.Mecha.ui = {};
    win.Mecha.ui.refresh = function() {
        do_table_data();
        do_tree();
        do_tab();
        do_toggle();
        do_slug();
        do_description();
        do_item();
    };

    win.Mecha.ui.refresh();

})(DOM, window, document);