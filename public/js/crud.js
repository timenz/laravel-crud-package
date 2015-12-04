String.prototype.isValidLocation = function(){
    var out  = this.split(',');

    if(out.length !== 2){
        return false;
    }

    var lat = parseFloat(out[0]);
    var lng = parseFloat(out[1]);

    if(!(lat <= 90 && lat >= -90 && lng <= 180 && lng >= -180)){
        return false;
    }

    return true;
};

Number.prototype.formatMoney = function(decPlaces, thouSeparator, decSeparator) {
    var n = this,
        decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces,
        decSeparator = decSeparator == undefined ? "." : decSeparator,
        thouSeparator = thouSeparator == undefined ? "," : thouSeparator,
        sign = n < 0 ? "-" : "",
        i = parseInt(n = Math.abs(+n || 0).toFixed(decPlaces)) + "",
        j = (j = i.length) > 3 ? j % 3 : 0;
    return sign + (j ? i.substr(0, j) + thouSeparator : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thouSeparator) + (decPlaces ? decSeparator + Math.abs(n - i).toFixed(decPlaces).slice(2) : "");
};

function parse_float(string){
    var val = parseFloat(string.replace(/,/gi, ''));
    if(isNaN(val)){
        return 0;
    }else{
        return val;
    }
}

function form_serialize_convert(serial) {
    var ret = {};
    for (i in serial) {
        var row = serial[i];
        ret[row.name] = row.value
    }
    return ret;
}

function print_page(dvprintid) {
    var prtContent = document.getElementById(dvprintid);
    var WinPrint = window.open('', '', 'letf=100,top=100,width=800,height=600');
    WinPrint.document.write(prtContent.innerHTML);
    WinPrint.document.close();
    WinPrint.focus();
    WinPrint.print();
    //WinPrint.close();
}

var substringMatcher = function(strs) {
    return function findMatches(q, cb) {
        var matches, substrRegex;

        // an array that will be populated with substring matches
        matches = [];

        // regex used to determine if a string contains the substring `q`
        substrRegex = new RegExp(q, 'i');

        // iterate through the pool of strings and for any string that
        // contains the substring `q`, add it to the `matches` array
        $.each(strs, function(i, str) {
            if (substrRegex.test(str)) {
                // the typeahead jQuery plugin expects suggestions to a
                // JavaScript object, refer to typeahead docs for more info
                matches.push({ value: str });
            }
        });

        cb(matches);
    };
};