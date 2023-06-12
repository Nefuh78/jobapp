"use strict";

let first_date = '';
let last_date = '';

$(function(){
    update_time();
    window.setInterval(update_time, 999);
    load_data();
})

function load_data() {
    first_date = document.getElementById('start_date').value;
    last_date = document.getElementById('end_date').value;
    get_top_10_most_buyed_products(first_date, last_date);
    get_top_10_worst_buyed_products(first_date, last_date);
    get_revenue_by_years(first_date, last_date);
    get_revenue_by_month(first_date, last_date);
    get_missing_descriptions();
    get_order_stats(first_date, last_date);
    get_defect_articles();
}

function update_time() {
    var current_date = new Date();
    var num_day = current_date.getDay();
    if (num_day == 0) var cdayname = 'Sonntag';
    else if (num_day == 1) var cdayname = 'Montag';
    else if (num_day == 2) var cdayname = 'Dienstag';
    else if (num_day == 3) var cdayname = 'Mittwoch';
    else if (num_day == 4) var cdayname = 'Donnerstag';
    else if (num_day == 5) var cdayname = 'Freitag';
    else if (num_day == 6) var cdayname = 'Samstag';
    var cday = current_date.getDate();
    var cmonth = current_date.getMonth();
    var cyear = current_date.getFullYear();
    var chour = current_date.getHours();
    var cminutes = current_date.getMinutes();
    var cseconds = current_date.getSeconds(); 
    if (cseconds < 10) cseconds = '0'+cseconds;
    if (cminutes < 10) cminutes = '0'+cminutes;
    if (chour < 10) chour = '0'+chour;
    if (cmonth < 10) cmonth = '0'+cmonth;
    $('#headerdate').empty().append(cdayname + ', '+cday+'.'+cmonth+'.'+cyear);
    $('#headertime').empty().append(chour+':'+cminutes+':'+cseconds+' Uhr');
}

function get_loader() {
    let html = '<div class="loader p-5 text-center">'+"\n";
    html += '   <img src="./template/images/loader.gif" alt="Ladeanimation" />'+"\n";
    html += '   <br>Lade Daten...<br>Bitte warten.'+"\n";
    html += '</div>';
    return html;
}

function get_top_10_most_buyed_products(first_date, last_date) {
    $('#top_buyed_products').empty().append(get_loader());
    $.ajax({
        method: "POST",
        url: "index.php",
        data: { action: "get_top_10_most_buyed_products", start:first_date, end:last_date }
    })
    .done(function( data ) {
        let obj = JSON.parse(data);
        let html = '<div class="row fw-bold border-bottom">'+"\n";
        html += '   <div class="col-3">Art.Nr.</div>'+"\n";
        html += '   <div class="col">Artikelname</div>'+"\n";
        html += '   <div class="col text-end">Menge</div>'+"\n";
        html += '</div>';
        for (let i in obj) {
            let current = obj[i];
            html += '<div class="row article_row" onclick="get_article_details('+current['kArtikel']+');">'+"\n";
            html += '   <div class="col-3">'+current['cArtNr']+'</div>'+"\n";
            html += '   <div class="col">'+current['cName']+'</div>'+"\n";
            html += '   <div class="col-2 text-end">'+current['fAnzahl']+' ' + current['cEinheit'] + '</div>'+"\n";
            html += '</div>';
        }
        $('#top_buyed_products').empty().append(html);
    });    

}

function get_top_10_worst_buyed_products(first_date, last_date) {
    $('#worst_buyed_products').empty().append(get_loader());;
    let html = '<div class="row fw-bold border-bottom">'+"\n";
    html += '   <div class="col-3">Art.Nr.</div>'+"\n";
    html += '   <div class="col">Artikelname</div>'+"\n";
    html += '   <div class="col text-end">Menge</div>'+"\n";
    html += '</div>';
    $.ajax({
        method: "POST",
        url: "index.php",
        data: { action: "get_top_10_worst_buyed_products", start:first_date, end:last_date }
    })
    .done(function( data ) {
        let obj = JSON.parse(data);
        for (let i in obj) {
            let current = obj[i];
            html += '<div class="row article_row" onclick="get_article_details('+current['kArtikel']+');">'+"\n";
            html += '   <div class="col-3">'+current['cArtNr']+'</div>'+"\n";
            html += '   <div class="col">'+current['cName']+'</div>'+"\n";
            html += '   <div class="col-2 text-end">'+current['fAnzahl']+' ' + current['cEinheit'] + '</div>'+"\n";
            html += '</div>';
        }
        $('#worst_buyed_products').empty().append(html);
    });    
}

function get_revenue_by_years(first_date, last_date) {
    let label_array = new Array();
    let data_array = new Array();
    let data_array2 = new Array();
    let data_label = 'Netto';
    let data_label2 = "Brutto";
    $('#revenue_year').empty().append(get_loader());;
    $.ajax({
        method: "POST",
        url: "index.php",
        data: { action: "get_revenues_by_years", start:first_date, end:last_date }
    })
    .done(function( data ) {
        let obj = JSON.parse(data);
        let html = '<canvas id="test_diagram" style="max-width:689px; max-height:180px;"></canvas>' + "\n";
        html += '<div class="row fw-bold border-bottom">'+"\n";
        html += '   <div class="col-2">Jahr</div>'+"\n";
        html += '   <div class="col text-end">Netto</div>'+"\n";
        html += '   <div class="col text-end">Brutto</div>'+"\n";
        html += '</div>'+"\n";
        for (let i in obj) {
            let current = obj[i];
            label_array.push(i);
            data_array.push(current['netto']); 
            data_array2.push(current['brutto']);
            html += '<div class="row">'+"\n";
            html += '   <div class="col-2">'+i+'</div>'+"\n";
            html += '   <div class="col text-end">'+new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(current['netto'])+'</div>'+"\n";
            html += '   <div class="col text-end">'+new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(current['brutto'])+'</div>'+"\n";
            html += '</div>'+"\n";
        }
        $('#revenue_year').empty().append(html);
        create_diagram('test_diagram', label_array, data_label, data_array, data_label2, data_array2);
    });    
}

function create_diagram(canvas, labels_array, data_label, data_array, data_label2 = '', data_array2 = new Array()) {
    var chrt = document.getElementById(canvas);
    if (data_array2.length == 0 || data_array2.length == undefined) {
        var graph = new Chart(chrt, {
            type: 'bar',
            data: {
                labels: labels_array,
                datasets: [{
                    label: data_label,
                    data: data_array,
                    backgroundColor:["blue"],
                }],
            },
            options: {
                responsive: true,
            },
        });    
    } else {
        var graph = new Chart(chrt, {
        type: 'bar',
        data: {
            labels: labels_array,
            datasets: [{
                label: data_label,
                data: data_array,
                backgroundColor:["blue"],
            },{
                label: data_label2,
                data: data_array2,
                backgroundColor:["green"],
            }],
        },
        options: {
            responsive: true,
        },
        });
    }
}

function get_revenue_by_month(first_date, last_date) {
    $('#revenue_month').empty().append(get_loader());;
    $.ajax({
        method: "POST",
        url: "index.php",
        data: { action: "get_revenues_by_month", start:first_date, end:last_date }
    })
    .done(function( data ) {
        let obj = JSON.parse(data);
        let html = '<div class="accordion" id="revenue_accordion">';
        let num_years = Object.keys(obj).length;
        if (num_years > 1) $('#title_revenue_month').empty().append('Umsatz pro Monat pro Jahr');
        for (let year in obj) {
            let sum_year = new Array();
            sum_year['netto'] = 0;
            sum_year['brutto'] = 0;
            if (num_years > 1) {
                html += '   <div class="accordion-item">' + "\n";
                html += '       <h2 class="accordion-header">'+"\n";
                html += '           <button class="accordion-button collapsed" type="button" type="button" data-bs-toggle="collapse" data-bs-target="#accordion_'+year+'" aria-expanded="true" aria-controls="accordion_'+year+'">'+year+'</button>'+"\n";
                html += '       </h2>'+"\n";
                html += '       <div id="accordion_'+year+'" class="accorion-collapse collapse" data-bs-parent="#revenue_accordion"><div class="container">' + "\n";
            }
            else    
                html += '       '+year+'<br>' + "\n";
            html += '           <div class="row fw-bold border-bottom">'+"\n";
            html += '               <div class="col-2">&nbsp;</div>'+"\n";
            html += '               <div class="col text-end">Netto</div>'+"\n";
            html += '               <div class="col text-end">Brutto</div>'+"\n";
            html += '           </div>' + "\n";
            for (let month in obj[year]) {
                sum_year['netto'] += parseFloat(obj[year][month]['netto'].replace(' &euro;', '').replace(',', '.'));
                sum_year['brutto'] += parseFloat(obj[year][month]['brutto'].replace(' &euro;', '').replace(',', '.'));
                html += '           <div class="row">'+"\n";
                html += '               <div class="col-2">'+month+'</div>'+"\n";
                html += '               <div class="col text-end">'+obj[year][month]['netto']+'</div>'+"\n";
                html += '               <div class="col text-end">'+obj[year][month]['brutto']+'</div>'+"\n";
                html += '           </div>'+"\n";
            }
            html += '           <div class="row border-top pt-1 border-dark">'+"\n";
            html += '               <div class="col-2">Gesamt</div>'+"\n";
            html += '               <div class="col text-end">'+new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(sum_year['netto'])+'</div>'+"\n";
            html += '               <div class="col text-end">'+new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(sum_year['brutto'])+'</div>'+"\n";
            html += '           </div>'+"\n";
            if (num_years > 1) {
                html += '       </div></div>' + "\n";
                html += '   </div>' + "\n";
            }
        }
        html +='</div>' + "\n";
        $('#revenue_month').empty().append(html);
    });    
}

function get_missing_descriptions() {
    $('#missing_descriptions').empty().append(get_loader());
    $.ajax({
        method: "POST",
        url: "index.php",
        data: { action: "get_missing_descriptions" }
    })
    .done(function( data ) {
        let obj = JSON.parse(data);
        let html = '<div class="row fw-bold border-bottom">'+"\n";
        html += '   <div class="col-3">Art.Nr.</div>'+"\n";
        html += '   <div class="col text-end">Artikelname</div>'+"\n";
        html += '</div>';
        for (let i in obj) {
            let current = obj[i];
            html += '<div class="row" onclick="get_article_details('+current['kArtikel']+');">'+"\n";
            html += '   <div class="col-3">'+current['cArtNr']+'</div>'+"\n";
            html += '   <div class="col text-end">'+current['cName']+'</div>'+"\n";
            html += '</div>';
        }
        $('#missing_descriptions').empty().append(html);
    });    
}

function get_article_details(article_id) {    
    const articledetails_modal = new bootstrap.Modal(document.getElementById('articledetails_modal'));
    articledetails_modal.show();
    $('.modal-body').empty().append(get_loader());
    $.ajax({
        method: "POST",
        url: "index.php",
        data: { kArtikel: article_id, action: "get_article_details" }
    })
    .done(function( data ) {
        let html = data;
        $('.modal-body').empty().append(html);
    });    
}

function get_order_stats(first_date, last_date) {  
    $('#order_stats').empty().append(get_loader());
    $.ajax({
        method: "POST",
        url: "index.php",
        data: { action: "get_order_stats", start:first_date, end:last_date }
    })
    .done(function( data ) {
        let obj = JSON.parse(data);
        let html = '';
        html += '<div class="row">'+"\n";
        html += '   <div class="col">Ausgeliefert / Versendet</div>'+"\n";
        html += '   <div class="col">'+( (obj['KomplettGeliefert'] != undefined) ? obj['KomplettGeliefert'] : 'keine')+'</div>'+"\n";
        html += '</div>';
        html += '<div class="row">'+"\n";
        html += '   <div class="col">Teillieferungen</div>'+"\n";
        html += '   <div class="col">' + ((obj['Teillieferungen'] != undefined) ? obj['Teillieferungen'] : 'keine') +'</div>'+"\n";
        html += '</div>';
        html += '<div class="row">'+"\n";
        html += '   <div class="col">ohne Versand abgeschlossen</div>'+"\n";
        html += '   <div class="col">'+ ((obj['OhneVersandAbgeschlossen'] != undefined) ? obj['OhneVersandAbgeschlossen'] : 'keine')+'</div>'+"\n";
        html += '</div>';
        html += '<div class="row">'+"\n";
        html += '   <div class="col">offene Lieferungen</div>'+"\n";
        html += '   <div class="col">'+((obj['NichtGeliefert'] != undefined) ? obj['NichtGeliefert'] : 'keine')+'</div>'+"\n";
        html += '</div>';
        html += '<div class="row">'+"\n";
        html += '   <div class="col">Stornos</div>'+"\n";
        html += '   <div class="col">'+( (obj['Storno'] != undefined) ? obj['Storno'] : 'keine')+'</div>'+"\n";
        html += '</div>';
        $('#order_stats').empty().append(html);
    });    
}

function get_defect_articles() {  
    $('#defect_articles').empty().append(get_loader());  
    $.ajax({
        method: "POST",
        url: "index.php",
        data: { action: "get_defect_articles" }
    })
    .done(function( data ) {
        let obj = JSON.parse(data);
        let html = '<div class="row fw-bold border-bottom">'+"\n";
        html += '   <div class="col-3">Art.Nr.</div>'+"\n";
        html += '   <div class="col text-end">Artikelname</div>'+"\n";
        html += '</div>';
        for (let i in obj) {
            let current = obj[i];
            html += '<div class="row" onclick="get_article_details('+i+');">'+"\n";
            html += '   <div class="col-3">'+current['Artikelnummer']+'</div>'+"\n";
            html += '   <div class="col text-end">'+current['Artikelname']+'</div>'+"\n";
            html += '</div>';
        }
        $('#defect_articles').empty().append(html);
    });    

}