// -----------------------------------------------------------------------------------
//
//	Protolimit v1
//	A CoralFly Creation (www.coralfly.com)
//	Last Modified 12-19-2008
//
//	For Information, instructions, and new releases, visit:
//	http://www.coralfly.com/Creations/Protolimit
//
//	Licensed under the Creative Commons Attribution 2.5 License - http://creativecommons.org/licenses/by/2.5/
//  	- Free for use in both personal and commercial projects
//		- Attribution requires leaving author name, author link, and the license info intact
//
// -----------------------------------------------------------------------------------
var Protolimit = Class.create({
    initialize : function() {
        var ref = this;
        // Wire the Events
        $$('a[rel^=protolimit]').each(function(anchor){
            var rel = ref.parseRel(anchor.rel);
            if($(rel[0]) != null){
                ref.setRemainingCount(anchor,$(rel[0]),rel[1]);
                $(rel[0]).observe("keydown", function(limit){
                    return function(event){
                        ref.raiseLimitExceed(this,limit);
                    }
                }(rel[1]));
                $(rel[0]).observe("keyup", function(anchor, limit){
                    return function(){
                        ref.raiseLimitExceed(this,limit);
                        ref.setRemainingCount(anchor,this,limit);
                    }
                }(anchor,rel[1]));
            }
        });
    },
    // Parse the Rel attribute
     parseRel : function(value) {
        var relRegex = new RegExp("(\\w{1,})=(\\d{1,})");
        var result = relRegex.exec(value);
        return [result[1], result[2]];
    },
    // Sets the Remaining Count
    setRemainingCount : function(anchor,input,limit){
        anchor.innerHTML = limit-input.value.length;
    },
    // Limit Exceed Event
    raiseLimitExceed : function(obj,limit) {
        if(limit-obj.value.length < 0) {
            obj.value = obj.value.substring(0, limit);
        }
    }
});
document.observe('dom:loaded', function () { new Protolimit(); });