/**
 * author: Tobias Nickel
 * License: MIT, mach damit was du willst
 */
 
/**
 * Object like native JSON, it has the methods stringify and parse,
 * but it can store and restore circular structures
 */
tJSON = (function(){
    var refName='_tJSONR:';
    // ***************************************
    // parsing Methods
    // ***************************************
    var objects=[]; //paths and objects are two arrays, that are organized parrallel for mapping
    var json='';
    function stringifyObject(o,path){
        json+='{';
        var tmp=[];// contains [key,value];
        var first=true;
        for(var i in o){
            if(!o.hasOwnProperty(i))continue;
            if(first){ first=false; }else{ json+=',' }
            json+=JSON.stringify(i)+':';
            tJSON.stringify(o[i],path + '.' + i);
        }
        json+='}'
    }
    function stringifyArray(o,path){
        json+='[';
        for(var i=0;i<o.length;i++){
            if(i>0)json+=',';
            tJSON.stringify(o[i],path+'.'+i);
        }
        json+=']';
    }


    var shortest;
    function getShortestPath(o){
        return shortest.paths[shortest.objs.indexOf(o)];
    }
    function prepareShortestPaths(obj,path,objs,paths){
        if(typeof obj !== "object")return;

        path = path || "";
        objs=objs || [];// objs and paths are parrallel
        paths= paths || []; 
        var index = objs.indexOf(obj)
        if(index === -1){
            objs.push(obj)
            paths.push(path)
        }else{
            if(path.length < paths[index].length){
                paths[index] = path;
            }
            return {objs:objs,paths:paths};
        }
        for (var i in obj) {
            prepareShortestPaths(obj[i], path.length ? path+"."+i : i, objs,paths)   
        }
        return {objs:objs,paths:paths};

    }
    // ***************************************
    // parsing Methods
    // ***************************************
    var resolved=[]
    function resolveReferences(o,root){
        resolved.push(o);
        for(var i in o){
            if(typeof o[i] == 'string'){
                if(o[i].indexOf(refName)===0){
                    o[i] = getObj(root,o[i].slice(refName.length+1));
                }
            }else if(typeof o[i] == 'object'){
                if(resolved.indexOf(o[i]) === -1)
                    resolveReferences(o[i],root)
            }
        }
    }
    function getObj(root,path){
        var pathParts=path.split('.');
        var obj=root;
        var n='';
        while(n=pathParts.shift()){
            if(n.length){
                obj=obj[n];
            }
        }
        return obj;
    }
    return {
        stringify:function(o,path){
            var n=false;
            if(path===undefined){
                path =  '';
                objects=[];
                json='';
                n=true;
                shortest = prepareShortestPaths(o);
                var clearShortest=true;
            }
            switch(typeof o){
                case 'object':
                    var index=objects.indexOf(o);
                    if(index!==-1){
                        json+=JSON.stringify(refName + getShortestPath(o));
                    }else{
                        objects.push(o);
                        if(o instanceof Array){
                            stringifyArray(o,path);
                        }else{
                            stringifyObject(o,path);
                        }
                    }
                break;
                case 'function':
                case 'undefined':
                break;
                default:
                    json+=JSON.stringify(o);
            }
            if(n){
                // clean up some memory
                paths=[];  objects=[];
            }
            if(clearShortest)shortest = undefined;
            return json;
        },
        parse:function(s){
            var o=JSON.parse(s);
            resolved=[];
            resolveReferences(o,o);
            resolved=[];
            return o;
        }
    }
})()

