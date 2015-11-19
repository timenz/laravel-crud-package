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