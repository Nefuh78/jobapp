<?php

namespace nefuh\jtl;
use nefuh\framework\main;
use DateTime;

/**
 * JTL class for fetching data from JTL Wawi directly from the database.
 * This class best extends a class for processing the data received from database.
 * 
 * @author Joerg Hufen
 * @copyright Joerg Hufen, 2023
 * @package jtl
 * @version 1.0
 */
class jtl {

    public static $date_now = null;
    private static $defect_id = 2;
    

    // Wrapper function for local database connection
    private static function localdb() {
        global $localdb;
        return $localdb;
    }

    // Wrapper function for jtl database connection
    private static function db() {
        global $wawidb;
        return $wawidb;
    }
    
    /**
     * Fetch a number of articles from database
     * 
     * The function fetches first all products from JTL database and store it in a local database table for sorting.
     *
     * @param integer $num Number of articles to return
     * @param string $sort Sorting for fetching data from database
     * @param DateTime $start_date Start date 
     * @param DateTime $end_date End date
     * @return array article data
     */
    public static function get_products(int $num, string $sort, DateTime $start_date, DateTime $end_date):array {
        $return = [];
        self::localdb()->query('TRUNCATE top_articles');
        $data = self::db()->fetchAll('  SELECT 
                                            kArtikel, 
                                            cArtNr, 
                                            fAnzahl, 
                                            cName, 
                                            cEinheit 
                                        FROM Verkauf.tAuftragPosition AS tAuftragPosition 
                                        LEFT JOIN Verkauf.tAuftrag AS tAuftrag ON tAuftrag.kAuftrag = tAuftragPosition.kAuftrag 
                                        WHERE',['tAuftrag.dErstellt >=' => $start_date, 'tAuftrag.dErstellt <=' => $end_date]);
        foreach ($data as $entry) {
            if (!empty($entry['cArtNr'])) {
                $products[$entry['cArtNr']]['kArtikel'] = intval($entry['kArtikel']);
                $products[$entry['cArtNr']]['cArtNr'] = strval($entry['cArtNr']);
                $products[$entry['cArtNr']]['cName'] = strval($entry['cName']);
                $products[$entry['cArtNr']]['cEinheit'] = strval((isset($entry['cEinheit']) && !empty($entry['cEinheit'])) ? $entry['cEinheit'] : 'Stk.');
                if (isset($products[$entry['cArtNr']]['fAnzahl']) && $products[$entry['cArtNr']]['fAnzahl'] > 0)
                    $products[$entry['cArtNr']]['fAnzahl'] += floatval($entry['fAnzahl']);
                else    
                    $products[$entry['cArtNr']]['fAnzahl'] = floatval($entry['fAnzahl']);        
            }
        }
        foreach ($products as $product) {
            self::localdb()->query('REPLACE INTO top_articles ?', $product);
        }
        $data = self::localdb()->fetchAll('SELECT kArtikel, cArtNr, cName, fAnzahl, cEinheit FROM top_articles ORDER BY fAnzahl '.$sort.' LIMIT 0,'.$num);
        return $data;
    }

    /**
     * Get revenues for each year between start date and end date
     *
     * @param DateTime $start_date DateTime Object
     * @param DateTime $end_date DateTime Object
     * @return array Array by years
     */
    public static function get_revenues_by_years(DateTime $start_date, DateTime $end_date):array {
        $return = [];
        $data = self::db()->fetchAll("SELECT kAuftrag, dErstellt FROM Verkauf.tAuftrag WHERE",['tAuftrag.dErstellt >=' => $start_date, 'tAuftrag.dErstellt <=' => $end_date, 'nKomplettAusgeliefert' => 1]);
        if (isset($data) && !empty($data)) {
            foreach ($data as $order) {
                $order_data = self::db()->fetchAll("SELECT fWertRechnungNetto, fWertRechnungBrutto, fOffenerWert FROM Verkauf.tAuftragEckdaten WHERE kAuftrag = ?", $order['kAuftrag']);
                $year = $order['dErstellt']->format('Y');
                foreach ($order_data as $tmp_order) {
                    if (!isset($return[$year])) $return[$year] = ['netto' => 0, 'brutto' => 0];
                    $return[$year]['netto'] += floatval($tmp_order['fWertRechnungNetto']);
                    $return[$year]['brutto'] += floatval($tmp_order['fWertRechnungBrutto']); 
                }
            }
        }
        foreach ($return as $year => $values) {
            foreach ($values as $key => $val)
                $return[$year][$key] = number_format($val,2,'.', ''); 
        }
        return $return;
    }

    /**
     * Retrieve revenues for each month between start and end date, separated by year.
     *
     * @param DateTime $start_date DateTime object
     * @param DateTime $end_date DateTime object
     * @return array Array with revenues by year and month
     */
    public static function get_revenues_by_month(DateTime $start_date, DateTime $end_date):array {
        $return = [];
        $data = self::db()->fetchAll("SELECT kAuftrag, dErstellt FROM Verkauf.tAuftrag WHERE",['tAuftrag.dErstellt >=' => $start_date, 'tAuftrag.dErstellt <=' => $end_date, 'nKomplettAusgeliefert' => 1]);
        if (isset($data) && !empty($data)) {
            foreach ($data as $order) {
                $order_data = self::db()->fetchAll("SELECT fWertRechnungNetto, fWertRechnungBrutto, fOffenerWert FROM Verkauf.tAuftragEckdaten WHERE kAuftrag = ?", $order['kAuftrag']);
                $year = intval($order['dErstellt']->format('Y'));
                $month = intval($order['dErstellt']->format('m'));
                foreach ($order_data as $tmp_order) {
                    if (!isset($tmp[$year][$month])) $tmp[$year][$month] = ['netto' => 0, 'brutto' => 0];
                    $tmp[$year][$month]['netto'] += floatval($tmp_order['fWertRechnungNetto']);
                    $tmp[$year][$month]['brutto'] += floatval($tmp_order['fWertRechnungBrutto']); 
                }
            }
        }
        foreach ($tmp as $year => $values) {
            foreach ($values as $month => $month_values)
                foreach ($month_values as $key => $val)
                $return[$year][main::get_month_name($month)][$key] = number_format($val, 2, ',', '.').' &euro;'; 
        }
        ksort($return);
        return $return;
    }

    /**
     * Get first order date from orders in jtl wawi
     *
     * @return string Date as string in format Y-m-d
     */
    public static function get_first_order_date():string {
        $return = '';
        $data = self::db()->fetch('SELECT dErstellt FROM Verkauf.tAuftrag ORDER BY dErstellt ASC');
        if (isset($data['dErstellt']) && !empty($data['dErstellt'])) $return = $data['dErstellt']->format('Y-m-d');
        return $return;
    }

    /**
     * Get last order date from orders in jtl wawi
     *
     * @return string Date as string in format Y-m-d
     */
    public static function get_last_order_date():string {
        $return = '';
        $data = self::db()->fetch('SELECT dErstellt FROM Verkauf.tAuftrag ORDER BY dErstellt DESC');
        if (isset($data['dErstellt']) && !empty($data['dErstellt'])) $return = $data['dErstellt']->format('Y-m-d');
        return $return;
    }

    /**
     * Retrieve all products, that have no description.
     *
     * @return array Array with internal article id (kArtikel), article number and article name
     */
    public static function get_missing_descriptions():array {
        $return = [];
        $articles = (array) self::db()->fetchAll('  SELECT 
                                                        a.kArtikel, 
                                                        a.cArtNr, ab.cName 
                                                    FROM tArtikelBeschreibung AS ab 
                                                    LEFT JOIN tArtikel AS a ON a.kArtikel = ab.kArtikel 
                                                    WHERE ab.cBeschreibung IS NULL OR ab.cBeschreibung = \'\' AND kPlattform = 1');
        if (isset($articles) && !empty($articles)) $return = $articles;
        return $return;
    }

    /**
     * Retrieve more detailed informations about an article
     *
     * @param integer $kArtikel internal article id to retrieve informations
     * @return array Array with article data
     */
    public static function get_article_details(int $kArtikel):array {
        $return = [];
        $data = self::db()->fetch("SELECT 
                                    Artikelnummer, 
                                    Artikelname, 
                                    Einheit, 
                                    EAN, 
                                    Herkunftsland, 
                                    Hersteller, 
                                    Erstelldatum, 
                                    Bearbeitungsdatum, 
                                    Bearbeiter, 
                                    EinkaufspreisDurchschnittNetto, 
                                    EinkaufspreisLetzerEinkauf, 
                                    DatumLetzerEinkauf, 
                                    VerkaufspreisNetto,
                                    gewinn,
                                    ZustandName,
                                    GewinnInProzent,
                                    BestandGesamt,
                                    BestandVerfuegbar,
                                    MindestAbnahmemenge,
                                    Abnahmeintervall,
                                    cLagerfuehrungaktiv,
                                    cIstAufPreisliste,
                                    cIstAktiv,
                                    cIstTopArtikel,
                                    cIstNeu,
                                    cIstStueckzahlteilbar,
                                    cIstUeberverkaufMoeglich,
                                    VerkaufspreisBrutto,
                                    Anmerkung,
                                    Lieferant,
                                    Beschreibung,
                                    Kurzbeschreibung
                                FROM ArtikelVerwaltung.vArtikelliste                                 
                                WHERE kArtikel = ?", $kArtikel);
        $data['status'] = 'ok';
        if (isset($data) && !empty($data)) {
            $tmp = self::db()->fetch('SELECT SUM(fAnzahl) AS verkauft FROM Verkauf.tAuftragPosition WHERE kArtikel = ?', $kArtikel);
            $data['Beschreibung'] = self::get_article_description($kArtikel);
            $data['AnzahlVerkauft'] = number_format((isset($tmp['verkauft']) && !empty($tmp['verkauft'])) ? $tmp['verkauft'] : 0, 2, ',', '.');
            $data['AnzahlVerkauftAktJahr'] = self::get_num_sold_by_year($kArtikel, date('Y'));
            $data['Erstelldatum'] = $data['Erstelldatum']->format('d.m.Y');
            $data['Bearbeitungsdatum'] = (isset($data['Bearbeitungsdatum']) && !empty($data['Bearbeitungsdatum'])) ? $data['Bearbeitungsdatum']->format('d.m.Y H:i') : 0;
            $data['DatumLetzerEinkauf'] = (isset($data['DatumLetzerEinkauf']) && !empty($data['DatumLetzerEinkauf'])) ? $data['DatumLetzerEinkauf']->format('d.m.Y H:i') : 0;
            $data['EinkaufspreisDurchschnittNetto'] = floatval($data['EinkaufspreisDurchschnittNetto']);
            $data['EinkaufspreisLetzerEinkauf'] = floatval($data['EinkaufspreisLetzerEinkauf']);
            $data['VerkaufspreisNetto'] = floatval($data['VerkaufspreisNetto']);
            $data['gewinn'] = floatval($data['gewinn']);
            $data['VerkaufspreisBrutto'] = floatval($data['VerkaufspreisBrutto']);
            $data['GewinnInProzent'] = floatval(floatval($data['GewinnInProzent']));
            $data['Verkaufsdaten'] = self::get_article_selling_data($kArtikel);
            $data['Warengruppe'] = self::get_article_group($kArtikel);
            $return = (array) $data;
        }
        return $return;
    }

    /**
     * Internal function to get the article product group
     *
     * @param integer $kArtikel internal article id
     * @return string Name of the product group
     */
    private static function get_article_group(int $kArtikel):string {
        $return = 'Keine';
        $data = self::db()->fetch(' SELECT tWarengruppe.cName
                                    FROM tArtikel
                                    LEFT JOIN tWarengruppe ON tWarengruppe.kWarengruppe = tArtikel.kWarengruppe
                                    WHERE tArtikel.kArtikel = ?', $kArtikel);
        if (isset($data['cName']) && !empty($data['cName'])) $return = $data['cName'];
        return $return;
    }

    /**
     * Fetch description for an article
     *
     * @param integer $kArtikel internal article id
     * @return string The description for the article or empty if there's no description in the database.
     */
    private static function get_article_description(int $kArtikel):string {
        $return = '';
        $data = self::db()->fetch('SELECT cBeschreibung FROM tArtikelBeschreibung WHERE', ['kArtikel' => $kArtikel, 'kPlattform' => 1]);
        if (isset($data['cBeschreibung']) && !empty($data['cBeschreibung'])) $return = $data['cBeschreibung'];
        return $return;
    }

    /**
     * Retrieve the selling amount of an article for a submitted year.
     *
     * @param integer $kArtikel internal article id
     * @param string $year year to retrieve data for
     * @return float selling amount as float
     */
    private static function get_num_sold_by_year(int $kArtikel, string $year):float {
        $return = 0;
        $start_date = new DateTime($year.'-01-01 00:00:00');
        $end_date = new DateTime($year.'-12-31 00:00:00');
        $tmp = self::db()->fetch('  SELECT 
                                        SUM(Verkauf.tAuftragPosition.fAnzahl) AS verkauft 
                                    FROM Verkauf.tAuftragPosition 
                                    LEFT JOIN Verkauf.tAuftrag ON Verkauf.tAuftrag.kAuftrag = Verkauf.tAuftragPosition.kAuftrag
                                    WHERE',['Verkauf.tAuftragPosition.kArtikel' => $kArtikel, 'Verkauf.tAuftrag.dErstellt >=' => $start_date, 'Verkauf.tAuftrag.dErstellt <=' => $end_date]);
        if (isset($tmp['verkauft']) && !empty($tmp['verkauft'])) $return = floatval($tmp['verkauft']);
        return $return;
    }

    /**
     * Get multiple order stats for a given time period between start and end date.
     *
     * @param DateTime $start_date 
     * @param DateTime $end_date
     * @return array Stats
     */
    public static function get_order_stats(DateTime $start_date, DateTime $end_date):array {
        $return = [];
        $tmp = self::db()->fetch("SELECT COUNT(kAuftrag) AS num FROM Verkauf.tAuftrag WHERE", ['dErstellt >=' => $start_date, 'dErstellt <=' => $end_date, 'nKomplettAusgeliefert' => 1, 'nStorno' => 0]);
        if (isset($tmp['num']) && !empty($tmp['num'])) $return['KomplettGeliefert'] = intval($tmp['num']);
        $tmp = self::db()->fetch("SELECT COUNT(kAuftrag) AS num FROM Verkauf.tAuftrag WHERE", ['dErstellt >=' => $start_date, 'dErstellt <=' => $end_date, 'nKomplettAusgeliefert' => 0, 'nStorno' => 0]);
        if (isset($tmp['num']) && !empty($tmp['num'])) $return['NichtGeliefert'] = intval($tmp['num']);
        $tmp = self::db()->fetch("SELECT COUNT(kAuftrag) AS num FROM Verkauf.tAuftrag WHERE", ['dErstellt >=' => $start_date, 'dErstellt <=' => $end_date, 'nKomplettAusgeliefert' => 2, 'nStorno' => 0]);
        if (isset($tmp['num']) && !empty($tmp['num'])) $return['OhneVersandAbgeschlossen'] = intval($tmp['num']);
        $tmp = self::db()->fetch("SELECT COUNT(kAuftrag) AS num FROM Verkauf.tAuftrag WHERE", ['dErstellt >=' => $start_date, 'dErstellt <=' => $end_date, 'nStorno' => 1]);
        if (isset($tmp['num']) && !empty($tmp['num'])) $return['Storno'] = intval($tmp['num']);
        $tmp = self::db()->fetchAll("SELECT Verkauf.tAuftrag.kAuftrag FROM Verkauf.tAuftrag LEFT JOIN Verkauf.tAuftragPositionEckdaten ON Verkauf.tAuftragPositionEckdaten.kAuftrag = Verkauf.tAuftrag.kAuftrag WHERE", ['Verkauf.tAuftrag.dErstellt >=' => $start_date, 'Verkauf.tAuftrag.dErstellt <=' => $end_date, 'Verkauf.tAuftrag.nKomplettAusgeliefert' => 0, 'nStorno' => 0], 'GROUp BY Verkauf.tAuftrag.kAuftrag');
        if (isset($tmp) && !empty($tmp)) $return['Teillieferungen'] = count($tmp);
        return $return;
    }

    /**
     * Get an array with articles that are marked as defect.
     *
     * @return array 
     */
    public static function get_defect_articles():array {
        $return = [];
        $tmp = self::db()->fetchAll("SELECT kArtikel FROM dbo.tArtikel WHERE kZustand = ?", self::$defect_id);
        if (isset($tmp) && !empty($tmp))
            foreach ($tmp AS $article)
                $return[$article['kArtikel']] = self::get_article_details($article['kArtikel']);
        return $return;
    }

    /**
     * Get sellling data for an article
     *
     * @param int $kArtikel
     * @return array
     */
    private static function get_article_selling_data(int $kArtikel):array {
        $return = [];
        $data = (array) self::db()->fetchAll('  SELECT 
                                            Verkauf.tAuftragPositionEckdaten.kAuftrag,
                                            Verkauf.tAuftragPositionEckdaten.fAnzahlOffen, 
                                            Verkauf.tAuftragPositionEckdaten.fAnzahlGeliefert, 
                                            Verkauf.tAuftragPositionEckdaten.fAnzahlGutgeschrieben, 
                                            Verkauf.tAuftragPositionEckdaten.fAnzahlAufRechnung,
                                            Verkauf.tAuftragPositionEckdaten.fWertNetto,
                                            Verkauf.tAuftragEckdaten.dBezahlt,
                                            Verkauf.tAuftragEckdaten.dLetzterVersand,
                                            Verkauf.tAuftrag.dErstellt,
                                            Verkauf.tAuftrag.cAuftragsNr
                                        FROM Verkauf.tAuftragPositionEckdaten
                                        LEFT JOIN Verkauf.tAuftragEckdaten ON Verkauf.tAuftragEckdaten.kAuftrag = Verkauf.tAuftragPositionEckdaten.kAuftrag
                                        LEFT JOIN Verkauf.tAuftrag ON Verkauf.tAuftrag.kAuftrag = Verkauf.tAuftragPositionEckdaten.kAuftrag
                                        WHERE',['Verkauf.tAuftragPositionEckdaten.kArtikel' => $kArtikel, 'Verkauf.tAuftragEckdaten.nIstFuerAuslieferungGesperrt' => 0], 'ORDER BY Verkauf.tAuftrag.dErstellt DESC');
        if (isset($data) && !empty($data)) {
            foreach ($data as $row) {
                foreach ($row as $key => $value) {
                    if (($key == 'dLetzterVersand' || $key == 'dBezahlt' || $key == 'dErstellt') && ($value != null && gettype($value) == 'object'))
                            $value = $value->format('Y-m-d H:i:s');
                    $return[$row['kAuftrag']][$key] = $value;
                }
            }
        }
        return $return;
    }

    /**
     * Function to fetch stock data from articles for exporting into csv file, for use in Excel.
     *
     * @return array data array with stock articles sorted by article state
     */
    public static function get_stock_corrections():array {
        $return = [];
        $dataset = self::db()->fetchAll("  SELECT 
                                            tArtikel.cArtNr,
                                            tWarenLagerAusgang.kArtikel,
                                            tWarenLagerAusgang.fAnzahl AS Menge, 
                                            tWarenLagerAusgang.dErstellt AS Datum,
                                            tWarenLager.cName AS Lagername,
                                            tArtikelBeschreibung.cName AS Artikelname,
                                            tArtikel.kZustand AS Zustand,
                                            tBenutzer.cName AS Benutzer 
                                        FROM tWarenLagerAusgang 
                                        LEFT JOIN tWarenLagerPlatz ON tWarenLagerPlatz.kWarenLagerPlatz = tWarenLagerAusgang.kWarenLagerPlatz
                                        LEFT JOIN tWarenLager ON tWarenLager.kWarenLager = tWarenLagerPlatz.kWarenLager
                                        LEFT JOIN tArtikelBeschreibung ON tArtikelBeschreibung.kArtikel = tWarenLagerAusgang.kArtikel AND tArtikelBeschreibung.kSprache = 1 AND tArtikelBeschreibung.kPlattform = 1
                                        LEFT JOIN tArtikel ON tArtikel.kArtikel = tWarenLagerAusgang.kArtikel
                                        LEFT JOIN tBenutzer ON tBenutzer.kBenutzer = tWarenLagerAusgang.kBenutzer
                                        WHERE", ['dErstellt >=' => new DateTime('2019-01-01 00:00:00'), 'dErstellt <=' => new DateTime(date('Y-m-d 23:59:59'))], 'ORDER BY dErstellt ASC');
        if (isset($dataset) && !empty($dataset)) {
            $i = 0;
            $pre_state = 0;
            foreach ($dataset as $row) {
                if ($pre_state != [$row['Zustand']]) $i = 0;
                if ($i == 0) {
                    $data[$row['Zustand']][0] = ['Artikelnummer', 'Artikelname', 'DurschschnittEKNetto', 'Menge', 'EK Summe'];
                    $i++;
                }
                if (isset($data[$row['Zustand']][$row['kArtikel']]['Menge']) && !empty($data[$row['Zustand']][$row['kArtikel']]['Menge']))
                    $data[$row['Zustand']][$row['kArtikel']]['Menge'] += $row['Menge'];
                else
                    $data[$row['Zustand']][$row['kArtikel']]['Menge'] = floatval($row['Menge']);
                $sum_net = self::calc_average_purchase_price($row['kArtikel'], 1);
                $data[$row['Zustand']][$row['kArtikel']] = [
                    'Artikelnummer' => $row['cArtNr'],
                    'Artikelname' => $row['Artikelname'], 
                    'DurchschnittEKNetto' => self::calc_average_purchase_price($row['kArtikel'], $data[$row['Zustand']][$row['kArtikel']]['Menge']),
                    'Menge' => $data[$row['Zustand']][$row['kArtikel']]['Menge'], 
                    'EK Summe' => $sum_net
                ];
                $pre_state = [$row['Zustand']];
            }
        }     
        foreach ($data as $state => $rows) {
            foreach ($rows as $index => $values) {
                if ($index != 0) {
                    $values['DurchschnittEKNetto'] = number_format($values['DurchschnittEKNetto'], 4, ',', '.');
                    $values['Menge'] = number_format($values['Menge'], 4, ',', '.');
                    $values['EK Summe'] = number_format($values['EK Summe'], 4, ',', '.');
                }
                $return[$state][$index] = $values;
                
            }
        }
        unset($data, $state, $rows);
        return $return;                                   
    }

    /**
     * Retrieve average purchase price from supplier orders or if not suppliers orders available fetch default purchase price.
     *
     * @param int $kArtikel internal article id from JTL
     * @param int $menge the stock quantity of the article
     * @return float The (average) purchase price
     */
    private static function calc_average_purchase_price(int $kArtikel, int $menge):float {
        $return = 0;
        $data = self::db()->fetch("SELECT SUM(fEKNetto) AS fEKNetto FROM tLieferantenBestellungPos WHERE kArtikel = ?", $kArtikel);
        if (isset($data['fEKNetto']) && !empty($data['fEKNetto']))
            $return += ($data['fEKNetto'] / $menge);
        else {
            $data = self::db()->fetch("SELECT fEKNetto FROM tArtikel WHERE kArtikel = ?", $kArtikel);
            if (isset($data['fEKNetto']) && !empty($data['fEKNetto'])) $return = $data['fEKNetto'];
        }
        return (float) $return;
    }

}