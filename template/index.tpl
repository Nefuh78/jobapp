{include file="header.tpl"}
        <main class="container-float p-2">
            <div class="row pb-2">
                <div class="col">
                    <div class="card">
                        <div class="card-header">TOP 10 Verkaufte Produkte</div>
                        <div class="card-body" id="top_buyed_products"></div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-header">TOP 10 am wenigsten Verkaufte Produkte</div>
                        <div class="card-body" id="worst_buyed_products"></div>
                    </div>
                </div>
            </div>
            <div class="row pb-2">
                <div class="col">
                    <div class="card">
                        <div class="card-header">Umsatz pro Jahr</div>
                        <div class="card-body" id="revenue_year"></div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-header" id="title_revenue_month">Umsatz pro Monat</div>
                        <div class="card-body" id="revenue_month"></div>
                    </div>
                </div>
            </div>
            <div class="row pb-2">
                <div class="col">
                    <div class="card">
                        <div class="card-header">Artikel mit fehlender Beschreibung</div>
                        <div class="card-body" id="missing_descriptions"></div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-header">Bestellstatistiken</div>
                        <div class="card-body" id="order_stats"></div>
                    </div>
                </div>
            </div>
            <div class="row pb-2">
                <div class="col">
                    <div class="card">
                        <div class="card-header">Defekte Artikel</div>
                        <div class="card-body" id="defect_articles"></div>
                    </div>
                </div>
                <div class="col">&nbsp;</div>
            </div>
        </main>
{include file="footer.tpl"}