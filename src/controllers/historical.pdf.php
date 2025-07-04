<?php
include '../models/historical.class.php';

$pdf = new PDF('P', 'mm', 'letter');

$date = date("M 'y");
$client = $_POST['clients'];
$cliente = $client;
$pdf->setClient($client);
$totalsGlobal = $pdf->porcent();
$totalGlobal = $pdf->porcentBlank();
$date1 = $pdf->first_date();
$total = $pdf->totals();
$top = $pdf->top();
$artist = implode(', ', $top);
$blankT = number_format($totalsGlobal['por_blanks'],2).'%';
$blankWT = number_format($totalsGlobal['por_blanksWT'],2).'%';
$under300 = number_format($totalGlobal['por_300'],2).'%';
$_300_to_600 = number_format($totalGlobal['por_300_to_600'],2).'%';
$_600_to_1200 = number_format($totalGlobal['por_600_to_1200'],2).'%';
$over1200 = number_format($totalGlobal['por_1200'],2).'%';
$list = array('AMERICAN CLASSICS (FULL PACKAGE)',
              'BRAVADO (FULL PACKAGE)',
              'GLOBAL MERCHANDISING (FULL PACKAGE)',
              'MANHEAD (FULL PACKAGE)',
              'MERCH TRAFFIC (FULL PACKAGE)',
              'SONY MUSIC (FULL PACKAGE)',
              'WARNER (FULL PACKAGE)');

$pdf->SetTitle("$cliente Report");
$pdf->SetAuthor('Carlos Casillas');

$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 15);
$pdf->SetFont('Arial', '', 36);
$pdf->Ln(60);
$pdf->cell(0,10, 'Factory 1 Report', 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('', '', 24);
$pdf->cell(0, 10, $cliente, 0, 1, 'C');
$pdf->Image('../../assets/img/marca_agua.png', 50, 120, 100);
$pdf->AddPage();

$pdf->AddPage();
$pdf->startPageNums();
$pdf->SetFont('', 'B', 16);
$pdf->Cell(0, 10, 'Orders Received', 0, 1, 'C');
$pdf->TOC_Entry('Orders Received', 0);
$pdf->SetFont('', 'BI', 12);
$pdf->cell(0, 10, 'Incoming Orders', 0, 1);
$pdf->TOC_Entry('Incoming Orders', 1);
$pdf->SetFont('', '', 11);
$pdf->MultiCell(0, 5, "Since first Order received from $cliente we have Purchase Orders as Full Package until today. In Table 1 Orders Received you will see: Items, Blanks and Hits by year.");
$pdf->Ln(2);

$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->cell(0, 10, 'Table 1 Orders Received', 0, 1);

$pdf->SetX(23);
$pdf->table1();
$pdf->Ln();

$pdf->SetFont('', 'BI', 12);
$pdf->cell(0, 10, 'Store', 0, 1);
$pdf->TOC_Entry('Store', 1);
$pdf->SetFont('', '', 11);
$pdf->MultiCell(0, 5, 'Now we have divided by store and sorted by pieces (blanks) received in Table 2 Orders Received by Store and by year in Table 3 Orders Received by Store and Year.');
$pdf->Ln(2);

$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->cell(0, 10, 'Table 2 Orders Received by Store', 0, 1);

$pdf->SetX(20);
$pdf->table2();
if ($pdf->GetPageHeight() -$pdf->GetY() < 100) {
    $pdf->AddPage();
    $pdf->SetY(20);
} else {
    $pdf->Ln();
}

$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->cell(0, 10, 'Table 3 Orders Received by Store and Year', 0, 1);

$pdf->SetX(20);
$pdf->table3();
if ($pdf->GetPageHeight() -$pdf->GetY() < 170) {
    $pdf->AddPage();
} else {
    $pdf->Ln(2);
}

$pdf->SetFont('', 'BI', 12);
$pdf->cell(0, 10, 'Treatment and Printing', 0, 1,);
$pdf->TOC_Entry('Treatment and Printing', 1);
$pdf->SetFont('', '', 11);
$pdf->MultiCell(0, 5, "Relead to orders ($blankT), considering total blank received as main filter and divided by store we add percent for treatment and mill orders in Table 4 Retailer by treatment and printing. Orders that doesn't need treatment represent $blankWT for all incoming Orders.");
$pdf->Ln(1);

$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->cell(0, 10, 'Table 4 Retailer by Treatment and Printing', 0, 1);

$pdf->SetX(20);
$pdf->table4();
if ($pdf->GetPageHeight() -$pdf->GetY() < 160) {
    $pdf->AddPage();
} else {
    $pdf->Ln();
}

$pdf->SetFont('', 'BI', 12);
$pdf->cell(0, 10, 'Process', 0, 1,);
$pdf->SetFont('', '', 11);
$pdf->MultiCell(0, 5, 'All treated blanks are classified by orders quantity and treatment process in Table 5 Orders received with Treatment by process and Total qty.');
$pdf->Ln(2);

$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->cell(0, 10, 'Table 5 Orders Received with Treatment by Process and Total Qty', 0, 1);

$pdf->table5();
if ($pdf->GetPageHeight() -$pdf->GetY() < 160) {
    $pdf->AddPage();
} else {
    $pdf->Ln();
}

$pdf->SetFont('', 'BI', 12);
$pdf->cell(0, 10, 'Gender', 0, 1,);
$pdf->TOC_Entry('Gender', 1);
$pdf->SetFont('', '', 11);
$pdf->MultiCell(0, 5, 'We divided by gender and Total quantity in Table 6 Orders Received by Gender.');
$pdf->Ln(2);

$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->cell(0, 10, 'Table 6 Orders Received by Gender', 0, 1);

$pdf->table6();
$pdf->AddPage();

$pdf->SetY(22);
$pdf->SetFont('', '', 11);
$pdf->MultiCell(0, 5, 'Based on above numbers, will show you a quick recap by month and year for Blanks, Hits, Treatment and non-treatment orders.');
$pdf->Ln(1);

$pdf->blanks();
$pdf->Ln(2);
$pdf->hits();
$pdf->Ln(2);
$pdf->treatment();
$pdf->Ln(2);
$pdf->no_treatment();
$pdf->AddPage();

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('', 'BI', 12);
$pdf->cell(0, 10, 'Cancelled', 0, 1,);
$pdf->TOC_Entry('Cancelled', 1);
$pdf->SetFont('', '', 11);
$pdf->MultiCell(0, 5, 'In Table 7 Orders cancelled are orders that we do not continue with production process. Under each store there is the PO number and those with REQ are request that we never received a PO number.');
$pdf->Ln(2);

$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->cell(0, 10, 'Table 7 Orders Cancelled', 0, 1);

$pdf->table7();
$pdf->AddPage();

$pdf->SetFont('', 'BI', 12);
$pdf->cell(0, 10, 'Small Orders', 0, 1, 'C');
$pdf->TOC_Entry('Small Orders', 0);
$pdf->SetFont('', '', 11);
$pdf->Cell(0, 5, 'To define small orders, we have three concepts based on pieces per desing:', 0, 1);
$pdf->Ln();
$pdf->SetFont('', 'B', 14);
$pdf->Cell(10, 5, chr(149), 0, 0, 'R');
$pdf->SetFont('', '', 11);
$pdf->Cell(10, 5, 'Less than 1,200', 0, 1);
$pdf->SetFont('', 'B', 14);
$pdf->Cell(10, 5, chr(149), 0, 0, 'R');
$pdf->SetFont('', '', 11);
$pdf->Cell(10, 5, 'Less than 600', 0, 1);
$pdf->SetFont('', 'B', 14);
$pdf->Cell(10, 5, chr(149), 0, 0, 'R');
$pdf->SetFont('', '', 11);
$pdf->Cell(10, 5, 'Less than 300', 0, 1);
$pdf->Ln();
$pdf->MultiCell(0, 5, "For less than 1200 pieces we received from $cliente $_600_to_1200 from total orders. For less than 600 pieces we received $_300_to_600 from total orders. From less than 300 pieces we received $under300 from total orders. We received $over1200 for orders with designs over 1200 pieces. In Table 8 Orders by total quantity per month you will find designs percent and pieces that those designs represent.");

$pdf->AddPage('L');
$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->SetY(20);
$pdf->cell(0, 10, 'Table 8 Orders by Total Quantity per Year', 0, 1);
$pdf->table8();
$h = $pdf->GetPageHeight() - $pdf->GetY();
if ($pdf->GetPageHeight() - $pdf->GetY() <= 101) {
    $pdf->AddPage('L');
    $pdf->SetY(20);
} else {
    $pdf->Ln(2);
}

$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->cell(0, 10, 'Table 9 Orders by Total Quantity per Retailer', 0, 1);
$pdf->table9();
$pdf->AddPage();

$pdf->SetFont('', 'BI', 12);
$pdf->cell(0, 10, 'Blanks', 0, 1);
$pdf->TOC_Entry('Blanks', 0);
$pdf->SetFont('', '', 11);
$pdf->Cell(0, 5, 'In below tables you will see blanks received by color and blank style.');
$pdf->Ln();

$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->cell(0, 10, 'Table 10 Blanks by Color', 0, 1);
$pdf->table10();
if ($pdf->GetPageHeight() -$pdf->GetY() < 150) {
    $pdf->AddPage();
} else {
    $pdf->Ln();
}

$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->cell(0, 10, 'Table 11 Blanks by Style', 0, 1);
$pdf->table11();
$pdf->AddPage();

$pdf->SetFont('', 'B', 16);
$pdf->cell(0, 10, 'Sales', 0, 1, 'C');
$pdf->TOC_Entry('Sales', 0);

$pdf->SetFont('', 'BI', 12);
$pdf->cell(0, 10, 'Anual Sales', 0, 1);
$pdf->TOC_Entry('Anual Sales', 1);
$pdf->SetFont('', '', 11);
$pdf->MultiCell(0, 5, "For $cliente we have a total $total from $date1 to $date.");
$pdf->Ln(2);

$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->cell(0, 10, 'Table 12 Sales', 0, 1);
$pdf->table12();
$pdf->Ln();

$pdf->SetFont('', 'BI', 12);
$pdf->cell(0, 10, 'Monthly Sales', 0, 1);
$pdf->TOC_Entry('Monthly Sales', 1);
$pdf->SetFont('', '', 11);
$pdf->MultiCell(0, 5, "Based on monthly sales we have the follow Table 13.");
$pdf->Ln(2);

$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->cell(0, 10, 'Table 13 Monthly Sales', 0, 1);
$pdf->table13();
$pdf->Ln();

$pdf->SetFont('', '', 11);
$pdf->MultiCell(0, 5, "Current year sales against year and month goal.");
$pdf->Ln(2);

$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->cell(0, 10, 'Table 14 '.date('Y').' Goals', 0, 1);
$pdf->table14();

$pdf->AddPage();
$pdf->SetFont('', 'BI', 12);
$pdf->cell(0, 10, 'Sales Charts', 0, 1);
$pdf->TOC_Entry('Sales Charts', 1);
$pdf->SetFont('', '', 11);
$pdf->MultiCell(0, 5, "In the next charts you will see sales by month for each year.");
$pdf->Ln(2);
$pdf->chart();

$pdf->AddPage();
$pdf->SetFont('', 'BI', 12);
$pdf->cell(0, 10, 'Open Orders', 0, 1);
$pdf->TOC_Entry('Open Orders', 0);
$pdf->SetFont('', '', 11);
$pdf->Cell(0, 5, "In Table 15 Open Orders you will see all orders that are not SHIPPED or CANCELED in our WIP.");
$pdf->Ln();
$pdf->Cell(0, 5, 'This table include Dollars, Items, Hits and Units per Status and Store.');
$pdf->Ln();

$pdf->SetFont('', 'I', 9);
$pdf->SetTextColor(113,130,193);
$pdf->cell(0, 10, 'Table 15 Open Orders', 0, 1);
$pdf->table15();

if (in_array($client, $list)) {
    $pdf->AddPage();
    $pdf->SetFont('', 'BI', 12);
    $pdf->cell(0, 10, 'Artist', 0, 1);
    $pdf->TOC_Entry('Artist', 0);
    $pdf->SetFont('', '', 11);
    $pdf->MultiCell(0, 5, "We know how important music is for $cliente and we classified every incoming order by artist in Table 16 Incoming orders by Artist. These are sorted with $artist at top, and divided by treatment and mill orders (just printing).");
    $pdf->Ln(2);
    $pdf->SetFont('', 'I', 9);
    $pdf->SetTextColor(113,130,193);
    $pdf->cell(0, 10, 'Table 16 Incoming Orders by Artist', 0, 1);
    $pdf->table16();
}
$pdf->stopPageNums();
$pdf->insertTOC(2);
$temPDF = tempnam(sys_get_temp_dir(), 'temp_pdf');

$pdf->Output($temPDF, 'F');

$fpdi = new PDF('P', 'mm', 'letter');
$fpdi->SetTitle("$cliente Report");
$fpdi->SetAuthor('Carlos Casillas');
$pages = $fpdi->setSourceFile($temPDF);

$newPdfFile = tempnam(sys_get_temp_dir(), 'new_pdf');

for ($pageNumber = 1; $pageNumber <= $pages; $pageNumber++) {
    if ($pageNumber != $pages) {
        $templateId = $fpdi ->importPage($pageNumber);
        $size = $fpdi->getTemplateSize($templateId);
        $fpdi->AddPage($size['orientation'], $size);
        $fpdi->useTemplate($templateId);
    }
}

$fpdi->Output('I', "$cliente Report");

unlink($temPDF);
?>