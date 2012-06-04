<?php

  /*
   Plugin Name: Simple CSV table
   Plugin URI: http:/iworks.pl
   Description:
   Version: trunk
   Author: Marcin Pietrzak
   Author URI: http://iworks.pl/
  */

class Simple_CSV_Table
{
    function __construct()
    {
        add_shortcode( 'csv', array( &$this, 'shortcode_center' ) );
    }

    public function shortcode_center( $atts )
    {
        extract( shortcode_atts( array(
            'href'       => false,
            'title'      => false,
            'skipcolumn' => '',
            'header'     => true,
            'showlink'   => true
        ), $atts ) );
        $skipcolumn = preg_split( '/,/', preg_replace('/[^0-9^,]/', '', $skipcolumn ) );
        $upload_dir = wp_upload_dir();
        $file = dirname( dirname( $upload_dir['basedir'] ) ) . $href;
        if ( !is_file( $file ) ) {
            return;
        }
        $row = 0;
        $d = array();
        if (($handle = fopen( $file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                for ($c=0; $c < $num; $c++) {
                    $d[$row][$c] = $data[$c];
                }
                $row++;
            }
            fclose($handle);
        }
        $content = '';
        if ( $title ) {
            $content .= apply_filters( 'simple_csv_table_title', sprintf( '<h2>%s</h2>%s', apply_filters( 'the_title', $title ), "\n" ) );
        }
        $thead = '';
        if ( $header ) {
            $data = array_shift( $d );
            $thead .= apply_filters( 'simple_csv_table_thead_tag', '<thead>'."\n" );
            $thead .= '<tr>'."\n";
            $num = count($data);
            for ($c=0; $c < $num; $c++) {
                if ( in_array( $c, $skipcolumn ) ) {
                    continue;
                }
                $thead .= sprintf('<th>%s</th>%s', $data[ $c ], "\n" );
            }
            $thead .= '</tr>'."\n".'</thead>'."\n";
        }
        $tbody = '';
        $i = 0;
        foreach( $d as $data ) {
            $tbody .= apply_filters( 'simple_csv_table_tbody_tr', sprintf( '<tr%s>%s', $i++%2? ' class="alternate"':'', "\n" ), $i, $data );
            $num = count($data);
            for ($c=0; $c < $num; $c++) {
                if ( in_array( $c, $skipcolumn ) ) {
                    continue;
                }
                $tbody .= sprintf('<td%s>%s</td>%s', preg_match( '/^\d+$/', $data[ $c ] )? ' class="alignright"':'', $data[ $c ], "\n" );
            }
            $tbody .= '</tr>'."\n";
        }
        if ( $tbody ) {
            $content .= '<table>'."\n";
            if ( $title ) {
                $content .= apply_filters( 'simple_csv_table_caption', sprintf( '<caption>%s</caption>%s', $title, "\n" ) );
            }
            if ( $thead ) {
                $content .= apply_filters( 'simple_csv_table_thead', $thead );
            }
            $content .= apply_filters( 'simple_csv_table_tbody', sprintf( '<tbody>%s%s</tbody>%s', "\n", $tbody, "\n" ) );
            $content .= '</table>'."\n";
        }
        if ( $showlink ) {
            $link = sprintf(
                '<div class="file"><a href="%s" title="%s">%s</a></div>',
                $href,
                wptexturize( $title ),
                basename( $href )
            );
            $content .= apply_filters( 'simple_csv_table_link', $link );
        }
        return apply_filters( 'simple_csv_table_all', '<div class="simple_csv_table">'.$content.'</div>' );
    }
}
new Simple_CSV_Table;

