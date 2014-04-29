<?php
function TableStart () {
    
    return "\n<table class='table'>";
    
}

function TableHeaderRow($Headers) {
    

    foreach ($Headers as $Header) {
        
        $Columns .= "<td>$Header</td>";
        
    }
    
    return "<tr class='table-header'>$Columns</tr>";//headers
}

function TableRow ($Row) {
    
    foreach ($Row as $Column) {
        
        $Columns .= "<td>$Column</td>";
        
    }

    return "\n<tr class='table-row' onmouseover=\"this.className='table-row-hover'\" onmouseout=\"this.className='table-row'\">" . $Columns . "</tr><tr class='table-seperator'><td colspan='" . count ($Column) . "'></td></tr>";
}

function TableEnd () {
    
    return "</table>";
}
?>