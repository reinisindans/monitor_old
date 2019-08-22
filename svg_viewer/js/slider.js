var raster;
var layer;
var steps;
var map;
var osm,osm_farbe;
var layercontrol;
var baseMaps, baseMaps_farbe;

$(function opacity_slider(){
    $( "#opacity_slider" ).slider({
        orientation: "horizontal",
        range: "min",
        min: 0,
        max: 100,
        value: getOpacity()*100,
        step: 10,
        stop: function (event, ui) {
            var slider_value = ui.value / 100;

            $('.legende_line').find('i').css('opacity', slider_value);
            //If null set colored backlayer
            if(ui.value === 0){
                map.removeControl(layercontrol);
                layercontrol = L.control.layers(baseMaps_farbe,overlayMaps);
                layercontrol.addTo(map);
                if(map.hasLayer(osm)){
                    map.removeLayer(osm);
                    osm_farbe.addTo(map);
                }
                else if(map.hasLayer(satellite)){
                    map.removeLayer(satellite);
                    satellite_farbe.addTo(map);
                }
                else if(map.hasLayer(webatlas)){
                    map.removeLayer(webatlas);
                    webatlas_farbe.addTo(map);
                }
                else if(map.hasLayer(topplus)){
                    map.removeLayer(topplus);
                    topplus_farbe.addTo(map);
                }
                else{

                }
            }else{
                map.removeControl(layercontrol);
                layercontrol = L.control.layers(baseMaps,overlayMaps);
                layercontrol.addTo(map);
                if(map.hasLayer(osm_farbe)){
                    map.removeLayer(osm_farbe);
                    osm.addTo(map);
                }
                else if(map.hasLayer(satellite_farbe)){
                    map.removeLayer(satellite_farbe);
                    satellite.addTo(map);
                }
                else if(map.hasLayer(webatlas_farbe)){
                    map.removeLayer(webatlas_farbe);
                    webatlas.addTo(map);
                }
                else if(map.hasLayer(topplus_farbe)){
                    map.removeLayer(topplus_farbe);
                    topplus.addTo(map);
                }else{

                }
            }
            //set the parameter
            urlparamter.updateURLParameter('opacity',slider_value);
            setOpacity(slider_value);
        }
    });
});
//control the time managment
function initSpatialSlider(steps, slider) {

    steps_set = steps;

    var raumgl_param = urlparamter.getUrlParameter('rasterweite');
    var value_set = raumgl_param;

    if(!raumgl_param){
        value_set = 0;
        urlparamter.setUrlParameter('rasterweite',value_set);
    }

    slider.slider({
        orientation: "horizontal",
        min: 0,
        max: steps.length-1,
        value: value_set,
        step: 1,
        slide:function(){

        },
        stop: function (event, ui) {
            $.when(urlparamter.updateURLParameter('rasterweite',ui.value))
                .then(initRaster())
                .then(setHeader(getRaumgliederungID()));
        }
    });

    var labels = [];
    $.each(steps,function(key,value){labels.push(value.replace('Raster','').replace('m',''));});
    pips.set(slider,labels);
}
function getOpacity(){
    var opacity = urlparamter.getUrlParameter('opacity');
    var value = opacity;
    if(!opacity){
        urlparamter.setUrlParameter('opacity',0.8);
        value = 0.8;
    }
    return value;

}
function setOpacity(value){
    var value_set = getOpacity();
    if(value){
        value_set = value;
    }
    if(raeumlicheauswahl.getRaeumlicheGliederung()=== 'gebiete'){
        map.eachLayer(function(layer){
            try {
                if (typeof layer.feature.properties.ags !== 'undefined') {
                    layer.setStyle({fillOpacity:value_set});
                }
            }catch(err){}
        });
    }else if(raeumlicheauswahl.getRaeumlicheGliederung() === 'raster'){
        raster_group.eachLayer(function(layer){
            layer.setOpacity(value_set);
        });
    }
}
const zeitslider={
    jahre_set:'',
    param:'time',
    getContainerDOMObject:function(){
      $elem = $('#slider_zeit_container');
      return $elem;
    },
    getSliderDOMObject:function(){
      $elem = $( "#zeit_slider" );
      return $elem;
    },
    setParam:function(_value){
        urlparamter.setUrlParameter(this.param,_value);
    },
    updateParam:function(_value){
        urlparamter.updateURLParameter(this.param,_value);
    },
    init:function(jahre) {
          const object = this;
          var time_param = this.getTimeSet(),
              value_set=jahre.length-1,
              slider = this.getSliderDOMObject();

        //show the time container
        object.jahre_set= jahre;
        object.show();

      //special issues with messages-----
      //check if parameter is set
      if(!time_param){
          object.setParam(jahre[value_set]);
      }
      //time param is set
      else{
          if(jahre.length == 1){
              object.updateParam(jahre[value_set]);
              alertOneTimeShift();
          }
          else if($.inArray(parseInt(time_param),jahre)!= -1){
              object.updateParam(jahre[$.inArray(parseInt(time_param),jahre)]);
              value_set = $.inArray(parseInt(time_param),jahre);
          }
          else{
              if($.inArray(parseInt(time_param),jahre) == -1){
                  object.updateParam(jahre[value_set]);
                  alertNotInTimeShift();
              }
          }
      }

      //initializeFirstView the slider by given values
      slider
          .unbind()
          .slider({
              orientation: "horizontal",
              min: 0,
              max: jahre.length-1,
              step: 1,
              value: value_set,
              stop: function (event, ui) {
                  object.updateParam(jahre[ui.value]);
                  if (raeumlicheauswahl.getRaeumlicheGliederung() === 'gebiete') {
                      var time = object.getTimeSet(),
                          //disable SST and g50
                          stt = $('#Raumgliederung option[value="stt"]'),
                          g50 = $('#Raumgliederung option[value="g50"]');

                      if (time < 2014) {
                          stt.prop('disabled', true);
                          g50.prop('disabled', true);
                      } else {
                          stt.prop('disabled', false);
                          g50.prop('disabled', false);
                      }
                      //get the json and table
                      if (gebietsauswahl.countTags()==0) {
                          indikatorJSON.init();
                      }
                      else {
                          indikatorJSON.init(getRaumgliederungfeinID());
                      }
                  }else{
                      initRaster();
                  }
                  setHeader();
                  map.dragging.enable();
              }
          })
          .mouseenter(function () {
              map.dragging.disable();
          })
          .mouseleave(function() {
              map.dragging.enable();
          });
          pips.set(slider,jahre);
    },
    show:function(){
        this.getContainerDOMObject().show();
    },
    hide:function(){
        this.getContainerDOMObject().hide();
    },
    getTimeSet:function(){
        return urlparamter.getUrlParameter(this.param);
    }
};
const pips = {
    set:function(slider,labels){
        // Then you can give it pips and labels!
        slider.slider('pips', {
            first: 'label',
            last: 'label',
            rest: 'pip',
            labels: labels,
            prefix: "",
            suffix: ""
        });

        // And finally can add floaty numbers (if desired)
        slider.slider('float', {
            handle: true,
            pips: true,
            labels: labels,
            prefix: "",
            suffix: ""
        });
    }
};