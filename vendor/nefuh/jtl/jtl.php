<?php

namespace nefuh\jtl;
use nefuh\framework\main;
use DateTime;

/**
 * JTL class for fetching data from JTL Wawi directly from the database.
 * This class best extends a class for processing the data received from database.
 * 
 * @author Joerg Hufen
 * @copyright Joerg Hufen, 2022
 * @package jtl
 * @version 2.0
 */
class jtl {

    public static $date_now = null;
    private static $defect_id = 2;
    
    private static function localdb() {
        global $localdb;
        return $localdb;
    }

    private static function db() {
        global $wawidb;
        return $wawidb;
    }
    
    public static function get_top_products(int $num, string $sort, $start_date, $end_date):array {
        $return = [];
        self::localdb()->query('TRUNCATE top_articles');
        $data = self::db()->fetchAll('SELECT kArtikel, cArtNr, fAnzahl, cName, cEinheit FROM Verkauf.tAuftragPosition AS tAuftragPosition LEFT JOIN Verkauf.tAuftrag AS tAuftrag ON tAuftrag.kAuftrag = tAuftragPosition.kAuftrag WHERE',['tAuftrag.dErstellt >=' => $start_date, 'tAuftrag.dErstellt <=' => $end_date]);
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

    public static function get_revenues_by_years($start_date, $end_date):array {
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

    public static function get_revenues_by_month($start_date, $end_date):array {
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


    public static function get_first_order_date():string {
        $return = '';
        $data = self::db()->fetch('SELECT dErstellt FROM Verkauf.tAuftrag ORDER BY dErstellt ASC');
        if (isset($data['dErstellt']) && !empty($data['dErstellt'])) $return = $data['dErstellt']->format('Y-m-d');
        return $return;
    }

    public static function get_last_order_date():string {
        $return = '';
        $data = self::db()->fetch('SELECT dErstellt FROM Verkauf.tAuftrag ORDER BY dErstellt DESC');
        if (isset($data['dErstellt']) && !empty($data['dErstellt'])) $return = $data['dErstellt']->format('Y-m-d');
        return $return;
    }

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

    private static function get_article_group(int $kArtikel):string {
        $return = 'Keine';
        $data = self::db()->fetch(' SELECT tWarengruppe.cName
                                    FROM tArtikel
                                    LEFT JOIN tWarengruppe ON tWarengruppe.kWarengruppe = tArtikel.kWarengruppe
                                    WHERE tArtikel.kArtikel = ?', $kArtikel);
        if (isset($data['cName']) && !empty($data['cName'])) $return = $data['cName'];
        return $return;
    }

    private static function get_article_description(int $kArtikel):string {
        $return = '';
        $data = self::db()->fetch('SELECT cBeschreibung FROM tArtikelBeschreibung WHERE', ['kArtikel' => $kArtikel, 'kPlattform' => 1]);
        if (isset($data['cBeschreibung']) && !empty($data['cBeschreibung'])) $return = $data['cBeschreibung'];
        return $return;
    }

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

    public static function get_defect_articles():array {
        $return = [];
        $tmp = self::db()->fetchAll("SELECT kArtikel FROM dbo.tArtikel WHERE kZustand = ?", self::$defect_id);
        if (isset($tmp) && !empty($tmp))
            foreach ($tmp AS $article)
                $return[$article['kArtikel']] = self::get_article_details($article['kArtikel']);
        return $return;
    }

    private static function get_article_selling_data($kArtikel):array {
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
}