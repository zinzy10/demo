<?php
require_once __DIR__ . '/../../config/controlDB_PDO.php';

class dashData {

    private $obj;
    private $date;

    public function __construct() {
        $this->obj = new ControlDB();
        $this->date = isset($_GET['date']) ? $_GET['date'] : '0';
    }

    function capacity() {

        $query = "SELECT
                    SUM(CASE WHEN comments LIKE '%hits%' THEN substring(comments, locate('hits',comments)-2,1) * total_qty ELSE '1' * total_qty END) AS hits,
                    need_components_in_h
                FROM t_wip
                WHERE comments NOT LIKE '%ONLY%'
                AND order_status NOT IN('SHIPPED', 'CANCELED', 'PACKED')
                AND client NOT LIKE '%FACTORY 1%'
                AND client != 'FOS'
                AND need_components_in_h != ''
                GROUP BY need_components_in_h";

        $capacity = $this->obj->consultar($query);
        
        $data = array();
        foreach ($capacity AS $CTotal) {
            $total = $CTotal['hits'];
            $fecha = $CTotal['need_components_in_h'];

            $data[] = array('hits'=>$total, 'fecha'=>$fecha);
        }

        $new_array = array("data"=>$data);

        return json_encode($new_array);
    }

    function components() {

        $sql = "SELECT 
                    SUM(CASE WHEN po_recv_vs_cxl_date = 'COMPLETED' THEN 1 ELSE 0 END) AS completed_count,
                    SUM(CASE WHEN po_recv_vs_cxl_date != 'COMPLETED' THEN 1 ELSE 0 END) AS incomplete_count,
                    SUM(CASE WHEN po_recv_vs_cxl_date = 'COMPLETED' THEN CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE total_qty END ELSE 0 END) AS completed_hits,
                    SUM(CASE WHEN po_recv_vs_cxl_date != 'COMPLETED' THEN CASE WHEN comments LIKE '%hits%' THEN SUBSTRING(comments, LOCATE('hits', comments) - 2, 1) * total_qty ELSE total_qty END ELSE 0 END) AS incomplete_hits
                FROM t_wip 
                WHERE comments NOT LIKE '%ONLY%' 
                AND order_status NOT IN ('SHIPPED', 'CANCELED', 'PACKED') 
                AND need_components_in_h = :date";

        $query = $this->obj->consultar($sql,[':date'=>$this->date])[0];

        return json_encode([
            'style' => $query['completed_count'], 
            'hits' => $query['completed_hits'], 
            'styleIncomp' => $query['incomplete_count'], 
            'hitsIncomp' => $query['incomplete_hits']
        ]);
    }

    function tableComp() {

        $Qcomp = "SELECT
                    order_status,
                    ship_date,
                    client,
                    store,
                    po_lot,
                    po_cxl_date,
                    style,
                    description,
                    color,
                    gender,
                    CASE WHEN comments LIKE '%hits%' THEN substring(comments, locate('hits',comments)-2,1) * total_qty ELSE '1' * total_qty END AS hits,
                    treatment
                FROM t_wip INNER JOIN complementos_wip ON t_wip.id_wip = complementos_wip.id_wip_c
                WHERE comments NOT LIKE '%ONLY%'
                AND order_status NOT IN('SHIPPED', 'CANCELED', 'PACKED')
                AND client NOT LIKE '%FACTORY 1%'
                AND client != 'FOS'
                AND po_recv_vs_cxl_date = 'COMPLETED'
                AND need_components_in_h = :date
                ORDER BY ship_date ASC";

        $Tcomplete = $this->obj->consultar($Qcomp,[':date'=>$this->date]);

        $data = array();
        foreach ($Tcomplete AS $DataComp) {
            $status = $DataComp['order_status'];
            $ship = $DataComp['ship_date'];
            $client = $DataComp['client'];
            $store = $DataComp['store'];
            $order = $DataComp['po_lot'];
            $cxlDate = $DataComp['po_cxl_date'];
            $style = $DataComp['style'];
            $desc = $DataComp['description'];
            $color = $DataComp['color'];
            $gender =$DataComp['gender'];
            $hits = number_format($DataComp['hits']);
            $treatment = $DataComp['treatment'];

            $newDate = date("M-d-Y", strtotime($cxlDate));
            $newShip = !empty($ship) ? date("M-d-Y", strtotime($ship)) : "";
            $data[] = array($status,
                            $newShip,
                            $client,
                            $store,
                            $order,
                            $newDate,
                            $style,
                            $desc,
                            $color,
                            $gender,
                            $hits,
                            $treatment);
        }

        $new_array = array("data"=>$data);

        return json_encode($new_array);
    }

    function tableIncomp() {

        $Qincomplete = "SELECT
                            po_recv_vs_cxl_date,
                            order_status,
                            ship_date,
                            client,
                            store,
                            po_lot,
                            po_cxl_date,
                            style,
                            description,
                            color,
                            gender,
                            CASE WHEN comments LIKE '%hits%' THEN substring(comments, locate('hits',comments)-2,1) * total_qty ELSE '1' * total_qty END AS hits,
                            treatment
                        FROM t_wip INNER JOIN complementos_wip ON t_wip.id_wip = complementos_wip.id_wip_c
                        WHERE comments NOT LIKE '%ONLY%'
                        AND order_status NOT IN('SHIPPED', 'CANCELED', 'PACKED')
                        AND client NOT LIKE '%FACTORY 1%'
                        AND client != 'FOS'
                        AND po_recv_vs_cxl_date != 'COMPLETED'
                        AND need_components_in_h = :date
                        ORDER BY ship_date ASC";

        $Tincomplete = $this->obj->consultar($Qincomplete,[':date'=>$this->date]);

        $data = array();
        foreach ($Tincomplete AS $DataComp) {
            $approve = $DataComp['po_recv_vs_cxl_date'];
            $status = $DataComp['order_status'];
            $ship = $DataComp['ship_date'];
            $client = $DataComp['client'];
            $store = $DataComp['store'];
            $order = $DataComp['po_lot'];
            $cxlDate = $DataComp['po_cxl_date'];
            $style = $DataComp['style'];
            $desc = $DataComp['description'];
            $color = $DataComp['color'];
            $gender =$DataComp['gender'];
            $hits = number_format($DataComp['hits']);
            $treatment = $DataComp['treatment'];

            $newDate = date("M-d-Y", strtotime($cxlDate));
            $newShip = !empty($ship) ? date("M-d-Y", strtotime($ship)) : "";
            $data[] = array($approve,
                            $status,
                            $newShip,
                            $client,
                            $store,
                            $order,
                            $newDate,
                            $style,
                            $desc,
                            $color,
                            $gender,
                            $hits,
                            $treatment);
        }

        $new_array = array("data"=>$data);

        return json_encode($new_array);
    }
}
?>