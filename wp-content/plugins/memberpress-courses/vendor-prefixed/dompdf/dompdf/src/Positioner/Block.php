<?php
/**
 * @package dompdf
 * @link    https://github.com/dompdf/dompdf
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * Modified by Team Caseproof using {@see https://github.com/BrianHenryIE/strauss}.
 */
namespace memberpress\courses\Dompdf\Positioner;

use memberpress\courses\Dompdf\FrameDecorator\AbstractFrameDecorator;

/**
 * Positions block frames
 *
 * @package dompdf
 */
class Block extends AbstractPositioner
{

    function position(AbstractFrameDecorator $frame): void
    {
        $style = $frame->get_style();
        $cb = $frame->get_containing_block();
        $p = $frame->find_block_parent();

        if ($p) {
            $float = $style->float;

            if (!$float || $float === "none") {
                $p->add_line(true);
            }
            $y = $p->get_current_line_box()->y;
        } else {
            $y = $cb["y"];
        }

        $x = $cb["x"];

        $frame->set_position($x, $y);
    }
}
