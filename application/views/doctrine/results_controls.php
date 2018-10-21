				<hr>
				<h3 id="results">Results</h3>
				<div class="row">
					<div class="col-md-4 ui-input">
						<span>Order By</span><br>
						<select name="orderType" class="form-control" style="display:inline;">
							<?php
							foreach( $orderTypes as $orderType_value => $orderType_name )
							{
								echo '<option value="'.$orderType_value.'"';
								if( isset($orderType) && $orderType == $orderType_value) echo ' selected';
								echo '>'.$orderType_name."</option>\n";
							}
							?>
						</select>
					</div>
					<div class="col-md-4 ui-input">
						<span>Sort direction</span><br>
						<select name="orderSort" class="form-control" style="display:inline;">
							<option value="DESC"<?php if($orderSort=='DESC') echo ' selected'; ?>>Descending</option>
							<option value="ASC"<?php if($orderSort=='ASC') echo ' selected'; ?>>Ascending</option>
						</select>
					</div>
					<div class="col-md-4 ui-input">
						<span>Page Size</span><br>
						<select name="pageSize" class="form-control" style="display:inline;">
							<?php
							foreach( $pageSizes as $possible_pageSize )
							{
								echo '<option value="'.$possible_pageSize.'"';
								if( isset($pageSize) && $pageSize == $possible_pageSize) echo ' selected';
								echo '>'.$possible_pageSize."</option>\n";
							}
							?>
						</select>
					</div>
				</div>
				<br>