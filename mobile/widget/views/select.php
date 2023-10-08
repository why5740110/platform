<div class="screening" >
    <div class="zezao"></div>
    <div class="screeningPopul">

        <div class="sx_con_box">
            <ul>
                <li>
                    <div class="screenlist" id="screenlist">
                        <label <?php if (isset($sanjia) && $sanjia == 0): ?>class="on"<?php endif; ?> for=kind0><input
                                    type=radio class="zhi" name=kind value=0 id=kind0
                                    <?php if (isset($sanjia) && $sanjia == 0): ?>checked=checked<?php endif; ?>><span
                                    class=radioCore>不限</span></label>
                        <label <?php if (isset($sanjia) && $sanjia == 1): ?>class="on"<?php endif; ?> for=kind1><input
                                    type=radio class="zhi" name=kind value=1 id=kind1
                                    <?php if (isset($sanjia) && $sanjia == 1): ?>checked=checked<?php endif; ?>><span
                                    class=radioCore>主任医师</span></label>
                        <label <?php if (isset($sanjia) && $sanjia == 6): ?>class="on"<?php endif; ?> for=kind6><input
                                    type=radio class="zhi" name=kind value=6 id=kind6
                                    <?php if (isset($sanjia) && $sanjia == 6): ?>checked=checked<?php endif; ?>><span
                                    class=radioCore>副主任医师</span></label>
                        <label <?php if (isset($sanjia) && $sanjia == 3): ?>class="on"<?php endif; ?> for=kind3><input
                                    type=radio class="zhi" name=kind value=3 id=kind3
                                    <?php if (isset($sanjia) && $sanjia == 3): ?>checked=checked<?php endif; ?>><span
                                    class=radioCore>主治医师</span></label>
                        <label <?php if (isset($sanjia) && $sanjia == 4): ?>class="on"<?php endif; ?> for=kind4><input
                                    type=radio class="zhi" name=kind value=4 id=kind4
                                    <?php if (isset($sanjia) && $sanjia == 4): ?>checked=checked<?php endif; ?>><span
                                    class=radioCore>住院医师</span></label>

                    </div>
                </li>

            </ul>
            <div class="screenSubmit">确定</div>
        </div>

    </div>
</div>