/*
 * @Author: Ricardo Órfão
 * @Date:   2014-05-04 13:22:10
 * @Last Modified by:   Ricardo Órfão
 * @Last Modified time: 2014-09-27 15:49:03
 */

$(document).ready(function() {
    menu();
    heartbeat();
    setInterval(heartbeat, 60000);
    $(document).on('mousedown', fEsconderErro);
    $(document).on('mouseover', '.content', function(event) {
        if (event.handler !== true) {
            $('#menu').multilevelpushmenu('collapse');
            event.handler = true;
        }
    });
    $(document).on('change', '#chkInscValidas', function(event) {
        if (event.handler !== true) {
            var valor = $(this).prop("checked");
            $.ajax({
                type: "POST",
                url: "functions/funcoes_admin.php",
                dataType: "json",
                data: "valor=" + valor + "&tipo=alt_validar",
                beforeSend: function() {
                    showLoading();
                },
                success: function(dados) {
                    if (dados.sucesso === true) {} else {
                        $('.error').show().html('<span id="error">Algo correu mal</span>');
                        $("body").animate({
                            scrollTop: 0
                        });
                    }
                }
            });
            event.handler = true;
        }
        return false;
    });

    $('#menu ul li a').on('click', function() {
		if ($(this).attr('href') != '#') {
            var ext = $(this).attr('href').split('.');
            if (ext[1] == "php") {
				window.open($(this).attr('href'), '_self');
            } else {
                $(document).on('click', '.btnRadio', fRadioClick);
                $(document).on('click', '.label_chk', fChkClick);
                $(document).on('click', '.editableText', fEditableTextClick);
                despiste($(this).attr('href'));
            }
        }
    });

    var chartData = (function() {
        var json;
        $.ajax({
            type: "POST",
            url: "functions/funcoes_admin.php",
            dataType: "json",
            data: "tipo=dados_grafico",
            async: false,
            success: function(dados) {
                json = dados.dados_in;
            }
        });
        return {
            getJson: function() {
                if (json)
                    return json;
            }
        };
    })();

    AmCharts.ready(function () {
        var chart;
        var legend;
        // PIE CHART
        chart = new AmCharts.AmPieChart();
        chart.dataProvider = chartData.getJson();
        chart.titleField = "nome";
        chart.valueField = "tempo";
        chart.outlineColor = "#FFFFFF";
        chart.outlineAlpha = 0.4;
        chart.outlineThickness = 1;
        legend = new AmCharts.AmLegend();
        legend.align = "center";
        legend.markerType = "circle";
        legend.position = "right";
        legend.marginRight=80;
        legend.autoMargins=false;
        legend.valueText="[[value]] mn";
        chart.balloonText = "[[title]]<br><span style='font-size:14px'><b>[[value]] mn</b> ([[percents]]%)</span>";
        chart.addLegend(legend);
        chart.startEffect = "easeOutSine";
        chart.startDuration = 1;
        chart.labelsEnabled = false;
        chart.language="pt";
        chart.decimalSeparator=",";
        chart.thousandsSeparator=" ";
        chart.colors=["#FF0F00", "#FF6600"];
        chart.pullOutEffect="easeOutSine";
        chart.pullOutOnlyOne=true;
        chart.sequencedAnimation=false;
        chart.exportConfig = {
            menuTop: '0px',
            menuLeft: 'auto',
            menuRight: '0px',
            menuBottom: 'auto',
            menuItems: [{
                textAlign: 'center',
                onclick: function () {},
                icon: 'js/amcharts_3.11.1/amcharts/images/export.png',
                iconTitle: 'Guardar o gráfico como uma imagem',
                items: [{
                    title: 'PNG',
                    format: 'png'
                }]
            }],
            menuItemOutput:{
                fileName:"amChart"
            },
            menuItemStyle: {
                backgroundColor: 'transparent',
                rollOverBackgroundColor: '#EFEFEF',
                color: '#000000',
                rollOverColor: '#CC0000',
                paddingTop: '6px',
                paddingRight: '6px',
                paddingBottom: '6px',
                paddingLeft: '6px',
                marginTop: '0px',
                marginRight: '0px',
                marginBottom: '0px',
                marginLeft: '0px',
                textAlign: 'left',
                textDecoration: 'none'
            }
        };
        // this makes the chart 3D
        chart.depth3D = 5;
        chart.angle = 30;

        // WRITE
        chart.write("chartdiv");
    });
    
});