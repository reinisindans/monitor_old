$(function(){
    $(window).on('resize', function() {
        if($(".jq_dialog").is(":hidden")){
            map._onResize();
            page_init = true;
            mainView.restoreView();
        }
    });
});
const mainView = {
    splitter:'',
    splitter_width:null,
    initializeFirstView:function(){
        hideHeader();
        zeitslider.hide();
        if (localStorage.getItem('tour') !== 'shown') {
           alertWebTour();
            localStorage.setItem('tour', 'shown');
            return false;
        }else{
            startMap.set();
            return false;
        }
    },
    restoreView:function(){
        legende.Resize();
        if (this.getWidth() <= 1280) {
            this.initResponsiveView();
        }
        else if (this.getWidth() <= 1400) {
            this.initSplitterView();
            toggleToolbar();
            rightView.close();
        }
        else {
            this.initSplitterView();
            rightView.close();
        }
    },
    setSplitter:function(){
        //QUELLLE: https://github.com/jcubic/jquery.splitter
        this.splitter = $('#mapwrap').height('100%').split({
            orientation: 'vertical',
            limit: 10,
            onDrag: function(event) {
                legende.close();
            },
            onDragEnd: function (event) {
                table.reinitializeStickyTableHeader();
                if(checkOverlap($('.leaflet-control'),$('.indikator_header'))===true){
                    console.log("Overlapping");
                }
                if(leftView.getWidth() <= 300){
                    $('#indikator_header').hide();
                    zeitslider.hide();
                }else{
                    $('#indikator_header').show();
                    zeitslider.show();
                }
                map._onResize();
            }
        });
    },
    resizeSplitter:function(_width){
        const object = this;
        if(viewState.getViewState()==='mw') {
            let width = (object.getWidth()-_width),
                min_width = (object.getWidth()-450);
            table.reinitializeStickyTableHeader();
            if ($('#leftPane').width() <= 300) {
                $('#indikator_header').hide();
                $('#slider_zeit_container').hide();
            } else {
                $('#indikator_header').show().css("left", "20%");
                $('#slider_zeit_container').show().css("left", "20%");
            }
            if(width >= min_width){
                width= min_width;
            }
            object.splitter.position(width);
            if(table.isOpen()){
                legende.Resize();
            }
            table.reinitializeStickyTableHeader();
        }
    },
    initSplitterView:function(){
        const object = this;
        viewState.setViewState("mw");
        legende.init();
        if(raeumlicheauswahl.getRaeumlicheGliederung()==="gebiete"){
            let width = object.getWidth();
            $('#table_ags').addClass("collapsing");
            $('.left_content').show();
            //divider
            object.setSplitter();
            //set the splitter position
            if(object.splitter_width==null) {
                if (width <= 1024) {
                    object.splitter.position(width / 2);
                    $('.indikator_header').css("right", "20%");
                    $('#legende').css({"right": $('#rightPane').width() + 10, 'display': ''}).hide();
                    $('#legende_close').css("right", $('#rightPane').width() + 30);
                    $('#legende_button').css("right", $('#rightPane').width()).show();
                }
                else {
                    object.splitter.position(45 + width / 100 + "%");
                    $('#legende').css({"right": $('#rightPane').width() + 10, 'display': ''});
                    $('#legende_close').css("right", $('#rightPane').width() + 30);
                }
            }else{
                object.splitter.position(splitter_width);
            }

            object.splitter_width = $('.vsplitter').css('left');
        }
        //set the slider width
        $('.content').css("overflow-y","");
        //reset the bootom padding of the time slider

        indikatorauswahl.fill();
        indikatorauswahl.getDOMObject()
            .dropdown('refresh');
    },
    getWidth:function(){
        return $(window).width();
    },
    getHeight:function(){
        return $(window).height();
    },
    initResponsiveView(){
        viewState.setViewState("responsive");
        if(mainView.getWidth()<=500) {
            $('.content')
                .css("overflow-y","auto");
            indikatorauswahl.getDOMObject()
                .dropdown('refresh');
        }
        //set the Legende
        legende.close();
        //resize
        indikatorauswahl.fill();
        if(raeumlicheauswahl.getRaeumlicheGliederung()==='gebiete'){
            panner.init();
            //bind the scroll handeler
            //reset the bootom padding of the time slider
            $('#rightPane').css("width","");
            $('#table_ags').removeClass("collapsing");
            $('#mapwrap').removeClass('splitter_panel');
        }
        //CSS settings
        $('.right_content').css("display","none");
    }
};

const leftView ={
    getDOMObject:function(){
      $elem = $('.left_content');
      return $elem;
    },
    hide:function(){
        this.getDOMObject().hide();
    },
    getWidth:function(){
        return this.getDOMObject().width();
    },
    show:function(){
        this.getDOMObject().show();
    }
};
const rightView = {
    getDOMObject: function(){
        $elem = $('.right_content');
        return $elem;
    },
    getCloseIconObject:function(){
        $elem= $('#table_close');
        return $elem;
    },
    open:function(){
        const view = this;
        //show only the table view, if the user set a indicator
        if(typeof indikatorauswahl.getSelectedIndikator() !== 'undefined') {
            //set the mobile view
            if (viewState.getViewState() === "responsive") {
                if (!this.isVisible()) {
                    view.show();
                    leftView.hide();
                    view.getCloseIconObject().hide();
                    legende.remove();
                    panner.setMapBackground();
                } else {
                    view.hide();
                    leftView.show();
                    legende.getShowButtonObject().show();
                    panner.setTableBackground();
                    panner.init();
                }
            } else {
                $('#mapwrap').addClass('splitter_panel');
                view.show();
                panner.hide();
                view.getCloseIconObject().show();
                mainView.resizeSplitter(table.getWidth());
            }
        }else{
            alertNoIndicatorChosen();
        }
        //bind the close icon

        //disable divider
        view.getCloseIconObject()
            .unbind()
            .click(function(){
                view.close();
            });
    },
    close:function(){
        this.hide();
        $('#mapwrap').removeClass('splitter_panel');
        map._onResize();
        panner.init();
        legende.Resize();
    },
    isVisible:function(){
        let state = true;
        if(this.getDOMObject().is(':hidden')){
            state = false;
        }
        return state;
    },
    hide:function(){
        this.getDOMObject().hide();
    },
    show:function(){
        indicatorJSONGroup.fitBounds();
        this.getDOMObject().show();
    },
    getWidth:function(){
       return this.getDOMObject().width();
    }
};
const viewState = {
  state: "mw",
  setViewState:function(_state){
      this.state = _state;
  },
  getViewState:function(){
      return this.state;
  }
};
//panner to open the left View
const panner = {
    getObject:function(){
        $elem = $('.panner');
        return $elem;
    },
    getContainer:function(){return $('.panner')},
    hide:function(){
        this.getContainer().hide();
    },
    show:function(){
        this.getContainer().show();
    },
    init:function(){
        if(raeumlicheauswahl.getRaeumlicheGliederung()!=='raster') {
            this.show();
            if(this.getObject().hasClass('mapbackground')){
                this.getObject().removeClass('mapbackground').addClass('tablebackground');
            }
            //bind the click functionality
            this.getObject()
                .unbind()
                .click(function(){
                    rightView.open();
                });
        }else{
            this.hide();
        }
    },
    setTableBackground:function(){
        this.getObject().removeClass('mapbackground').addClass('tablebackground');
    },
    setMapBackground:function(){
        $('.tablebackground').toggleClass('mapbackground');
    },
    isVisible:function(){
        let state = false;
        if(this.getObject().is(":visible")){
            state = true;
        }
        return state;
    }
};
const progressbar ={
    active: false,
    getContainer:function(){return $('#progress_div');},
    getTextContainer:function(){return $('#progress_header');},
    init:function(){
        if(this.active===false) {
            console.log("Init Progress Bar");
            $('body').append('<div id="progress_div"><h2 id="progress_header"></h2><div class="progress"></div></div>');
            this.getContainer().show();
            modal_layout.init();
            this.active = true;
        }
    },
    remove:function(callback){
        console.log("removeProgressBar()");
        modal_layout.remove();
        this.active = false;
        this.getContainer().remove();
        if(callback)callback();
    },
    setHeaderText:function(html_string){
        console.log("removeProgressBar: " + html_string);
        this.getTextContainer()
            .empty()
            .text(html_string)
    }
};
const modal_layout ={
    getJQueryObject:function(){return $('#Modal');},
    init:function(){
        this.getJQueryObject().css(
            {
                "position":"absolute",
                "width":"100%",
                "height":"100%",
                "background-color":"#000000",
                "filter":"alpha(opacity=60)",
                "opacity":"0.1",
                "-moz-opacity":"0.6",
                "z-index":"5000 !important",
                "text-align":"center",
                "vertical-align":"middle",
                "user-select": "none"
            }
        );
    },
    remove:function(){
        this.getJQueryObject().css(
            {
                "position":"",
                "width":"",
                "height":"",
                "background-color":"",
                "filter":"",
                "opacity":"",
                "-moz-opacity":"",
                "z-index":"",
                "text-align":"",
                "vertical-align":"",
                "pointer-events": ""
            }
        );
    }
};