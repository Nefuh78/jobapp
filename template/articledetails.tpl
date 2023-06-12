            <div class="card">
                <div class="card-header text-center fw-bold">{if $data['ZustandName'] == 'Defekt'}<i class="fa-solid fa-triangle-exclamation fa-beat pe-3" style="color: #ff0000;"></i>{/if}Artikeldaten für Artikelnummer {$data['Artikelnummer']} - {$data['Artikelname']}</div>
                <div class="card-body">
                    <div class="row border-bottom pb-2">
                        <div class="col-2 m-auto border-end">
                            {if $show_article_image == true}
                                <img src="./template/images/{$data['Artikelnummer']}-1.jpg" alt="{$data['Artikelnummer']}-1.jpg" class="articledetails_image" style="max-height:30vh; max-width:100%;" />
                            {else}
                                <img src="./template/images/no-image.png" alt="Kein Bilder verfügbar" class="articledetails_image" style="max-height:30vh; max-width:100%;" />
                            {/if}
                        </div>
                        <div class="col description_tabs">
                            <ul class="nav nav-tabs" id="articledetails_tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description-tab-pane" type="button" role="tab" aria-controls="description-tab-pane" aria-selected="true">Beschreibung</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="shortdescription-tab" data-bs-toggle="tab" data-bs-target="#shortdescription-tab-pane" type="button" role="tab" aria-controls="shortdescription-tab-pane" aria-selected="false">Kurzbeschreibung</button>
                                </li>
                            </ul>
                            <div class="tab-content" id="articledetails_tabs_content">
                                <div class="tab-pane fade show active p-2 text-start" id="description-tab-pane" role="tabpanel" aria-labelledby="description-tab" tabindex="0">
                                    {$data['Beschreibung']}
                                </div>
                                <div class="tab-pane fade p-2 text-start" id="shortdescription-tab-pane" role="tabpanel" aria-labelledby="shortdescription-tab" tabindex="0">
                                    {if isset($data['Kurzbeschreibung']) && !empty($data['Kurzbeschreibung'])}{$data['Kurzbeschreibung']}{else}<div class="fs-5 text-center">Keine Kurzbeschreibung vorhanden.</div>{/if}
                                </div>
                            </div>
                        </div>
                    </div>
                    <ul class="nav nav-tabs" id="articledata_tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="main-tab" data-bs-toggle="tab" data-bs-target="#main-tab-pane" type="button" role="tab" aria-controls="main-tab-pane" aria-selected="true">Stammdaten</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="selling-tab" data-bs-toggle="tab" data-bs-target="#selling-tab-pane" type="button" role="tab" aria-controls="selling-tab-pane" aria-selected="false">Verk&auml;ufe ({$data['Verkaufsdaten']|count})</button>
                    </li>
                </ul>
                <div class="tab-content" id="articledata_tabs_content">
                    <div class="tab-pane fade show active p-2 text-start" id="main-tab-pane" role="tabpanel" aria-labelledby="main-tab" tabindex="0">
                        <div class="fw-bold text-start">Stammdaten</div>
                        <div class="row mt-2 mb-1">
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_articlename">Artikelname</span>
                                    <input type="text" class="form-control" placeholder="Artikelname" aria-label="articlename" aria-describedby="title_articlename" value="{$data['Artikelname']}">
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_articlenumber">Artikelnummer</span>
                                    <input type="text" class="form-control" placeholder="Artikelnummer" aria-label="articlenumber" aria-describedby="title_articlenumber" value="{$data['Artikelnummer']}">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2 mb-1">
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_vendor">Hersteller</span>
                                    <input type="text" class="form-control" placeholder="Artikelname" aria-label="vendor" aria-describedby="title_vendor" value="{$data['Hersteller']}">
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_land">Herkunftsland</span>
                                    <input type="text" class="form-control" placeholder="Unbekannt" aria-label="land" aria-describedby="title_articlenumber" value="{$data['Herkunftsland']}">
                                </div>
                            </div>
                        </div>                       
                        <div class="row mt-2 mb-1">
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_group">Warengruppe</span>
                                    <input type="text" class="form-control" placeholder="Keine" aria-label="group" aria-describedby="title_group" value="{$data['Warengruppe']}">
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_ean">EAN</span>
                                    <input type="text" class="form-control" placeholder="" aria-label="ean" aria-describedby="title_ean" value="{$data['EAN']}">
                                </div>
                            </div>
                        </div>                       
                        <div class="fw-bold text-start">Einkaufspreis</div>
                        <div class="row mb-1">
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_last_purchase">Netto</span>
                                        <input type="text" class="form-control" placeholder="0,00 €" aria-label="last_purchase" aria-describedby="title_last_purchase" value="{if $data['EinkaufspreisLetzerEinkauf'] <> 0}{$data['EinkaufspreisLetzerEinkauf']|string_format:"%.2f"|replace:'.':','} &euro; {if $data['DatumLetzerEinkauf'] != 0}am {$data['DatumLetzerEinkauf']} Uhr{/if}{else}Kein Einkauf bisher{/if}">
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_average_purchase">&Oslash; (Netto)</span>
                                    <input type="text" class="form-control" placeholder="0,00 €" aria-label="average_purchase" aria-describedby="title_average_purchase" value="{$data['EinkaufspreisDurchschnittNetto']|string_format:"%.2f"|replace:'.':','} &euro;"> 
                                </div>
                            </div>
                        </div>
                        <div class="fw-bold text-start">Verkaufspreis</div>
                        <div class="row mb-1">
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_net_selling_price">Netto</span>
                                        <input type="text" class="form-control" placeholder="0,00 €" aria-label="net_selling_price" aria-describedby="title_net_selling_price" value="{$data['VerkaufspreisNetto']|string_format:"%.2f"|replace:'.':','} &euro;">   
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_selling_price">Brutto</span>
                                    <input type="text" class="form-control" placeholder="0,00 €" aria-label="selling_price" aria-describedby="title_selling_price" value="{$data['VerkaufspreisBrutto']|string_format:"%.2f"|replace:'.':','} &euro;">
                                </div>
                            </div>
                        </div>
                        <div class="fw-bold text-start">Gewinn</div>
                        <div class="row mb-1">
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_amount_profit">Betrag</span>
                                    <input type="text" class="form-control fw-bold{if $data['gewinn'] < 0} text-danger{elseif $data['gewinn'] == 0} text-warning{else}text-sucess{/if}" placeholder="0,00 €" aria-label="amount_profit" aria-describedby="title_amount_profit" value="{$data['gewinn']|string_format:"%.2f"|replace:'.':','} &euro;">
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_percent_profit">Prozentual</span>
                                    <input type="text" class="form-control" placeholder="0 %" aria-label="percent_profit" aria-describedby="title_percent_profit" value="{$data['GewinnInProzent']|string_format:"%.2f"|replace:'.':','} %">
                                </div>
                            </div>
                        </div>
                        <div class="fw-bold text-start">Lagerbestand</div>
                        <div class="row mb-1">
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_qty_stock">Gesamt</span>
                                    <input type="text" class="form-control fw-bold {if $data['BestandGesamt'] == 0}text-warning{elseif $data['BestandGesamt'] > 0}text-success{else}text-danger{/if}" placeholder="0" aria-label="qty_stock" aria-describedby="title_qty_stock" value="{$data['BestandGesamt']} {$data['Einheit']}">
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_qty_available">Verfügbar</span>
                                    <input type="text" class="form-control fw-bold {if $data['BestandVerfuegbar'] == 0}text-warning{elseif $data['BestandVerfuegbar'] > 0}text-success{else}text-danger{/if}" placeholder="0" aria-label="qty_available" aria-describedby="title_qty_available" value="{$data['BestandVerfuegbar']} {$data['Einheit']}">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_qty_sold">Verkauft (Gesamt)</span>
                                    <input type="text" class="form-control" placeholder="0" aria-label="qty_sold" aria-describedby="title_qty_sold" value="{$data['AnzahlVerkauft']} {$data['Einheit']}">
                                </div>
                            </div>
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_qty_sold_current_year">Verkauft akt. Jahr</span>
                                    <input type="text" class="form-control" placeholder="0" aria-label="qty_sold_current_year" aria-describedby="title_qty_sold_current_year" value="{$data['AnzahlVerkauftAktJahr']} {$data['Einheit']}">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text w-50" id="title_state">Zustand</span>
                                    <input type="text" class="form-control fw-bold{if $data['ZustandName'] == 'Defekt'} text-danger{else} text-success{/if}" placeholder="0" aria-label="state" aria-describedby="title_state" value="{$data['ZustandName']}">
                                </div>
                            </div>
                            <div class="col">&nbsp;</div>
                        </div>
                    </div>
                    <div class="tab-pane fade p-2 text-start" id="selling-tab-pane" role="tabpanel" aria-labelledby="selling-tab" tabindex="0">
                        <div class="row fw-bold border-bottom pb-2">
                            <div class="col-2">Auftrag</div>
                            <div class="col-3">Datum</div>
                            <div class="col">Offen</div>
                            <div class="col">Geliefert</div>
                            <div class="col">auf Rechnung</div>
                            <div class="col text-end">Netto VK</div>
                        </div>
                        {foreach from=$data['Verkaufsdaten'] item=$daten}
                            <div class="row">
                                <div class="col-2">{$daten.cAuftragsNr}</div>
                                <div class="col-3">{if !empty($daten.dBezahlt)}{$daten.dErstellt|date_format:"%d.%m.%Y %H:%I:%S"} Uhr{/if}</div>
                                <div class="col">{$daten.fAnzahlOffen|string_format:"%.2f"|replace:'.':','}  {$data['Einheit']}</div>
                                <div class="col">{$daten.fAnzahlGeliefert|string_format:"%.2f"|replace:'.':','} {$data['Einheit']}</div>
                                <div class="col">{$daten.fAnzahlAufRechnung|string_format:"%.2f"|replace:'.':','} {$data['Einheit']}</div>
                                <div class="col text-end">{$daten.fWertNetto|string_format:"%.2f"|replace:'.':','} &euro;</div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>