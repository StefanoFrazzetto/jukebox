<div id="keyboardWell">
    <ul id="keyboard">
        <li class="symbol"><span class="off">`</span><span class="on">~</span></li>
        <li class="symbol"><span class="off">1</span><span class="on">!</span></li>
        <li class="symbol"><span class="off">2</span><span class="on">&quot;</span></li>
        <li class="symbol"><span class="off">3</span><span class="on">#</span></li>
        <li class="symbol"><span class="off">4</span><span class="on">$</span></li>
        <li class="symbol"><span class="off">5</span><span class="on">%</span></li>
        <li class="symbol"><span class="off">6</span><span class="on">^</span></li>
        <li class="symbol"><span class="off">7</span><span class="on">&amp;</span></li>
        <li class="symbol"><span class="off">8</span><span class="on">*</span></li>
        <li class="symbol"><span class="off">9</span><span class="on">(</span></li>
        <li class="symbol"><span class="off">0</span><span class="on">)</span></li>
        <li class="symbol"><span class="off">-</span><span class="on">_</span></li>
        <li class="symbol"><span class="off">=</span><span class="on">+</span></li>
        <li class="delete lastitem"><i class="fa fa-long-arrow-left"></i></li>
        <li class="tab"><i class="fa fa-arrows-h"></i></li>
        <li class="letter">q</li>
        <li class="letter">w</li>
        <li class="letter">e</li>
        <li class="letter">r</li>
        <li class="letter">t</li>
        <li class="letter">y</li>
        <li class="letter">u</li>
        <li class="letter">i</li>
        <li class="letter">o</li>
        <li class="letter">p</li>
        <li class="symbol"><span class="off">[</span><span class="on">{</span></li>
        <li class="symbol"><span class="off">]</span><span class="on">}</span></li>
        <li class="symbol lastitem"><span class="off">\</span><span class="on">|</span></li>
        <li class="capslock"><i class="fa fa-lock"></i></li>
        <li class="letter">a</li>
        <li class="letter">s</li>
        <li class="letter">d</li>
        <li class="letter">f</li>
        <li class="letter">g</li>
        <li class="letter">h</li>
        <li class="letter">j</li>
        <li class="letter">k</li>
        <li class="letter">l</li>
        <li class="symbol"><span class="off">;</span><span class="on">:</span></li>
        <li class="symbol"><span class="off">'</span><span class="on">@</span></li>
        <li class="return lastitem">Enter &#10004;</li>
        <li class="left-shift"><i class="fa fa-arrow-up"></i></li>
        <li class="letter">z</li>
        <li class="letter">x</li>
        <li class="letter">c</li>
        <li class="letter">v</li>
        <li class="letter">b</li>
        <li class="letter">n</li>
        <li class="letter">m</li>
        <li class="symbol"><span class="off">,</span><span class="on">&lt;</span></li>
        <li class="symbol"><span class="off">.</span><span class="on">&gt;</span></li>
        <li class="symbol"><span class="off">/</span><span class="on">?</span></li>
        <li class="right-shift lastitem"><i class="fa fa-arrow-up"></i></li>
        <li class="space lastitem">&nbsp;</li>
    </ul>
</div>

<?php
use Lib\ICanHaz;

ICanHaz::js(['assets/js/keyboard.js', 'assets/js/draggable.js']);
?>