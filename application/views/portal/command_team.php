
			<h2>Command Team</h2>
			
			<?php
			$last_rank = NULL;
			foreach( $sorted_commanders as $FC )
			{
				$this_rank = $FC['Rank'];
				if( $this_rank !== $last_rank )
				{
					if( $last_rank != NULL )	// We were already in a rank div, close it before we open the next
					{
						echo "</div>\n";
					}
					echo '<div class="col-sm-4 entry-content">'."\n";
					echo '<h4>'.$rank_names[$this_rank]."</h4>\n";
				}
				echo '<a href="/activity/FC/'.$FC['UserID'].'">'.$FC['CharacterName']."</a><br>\n";
				$last_rank = $this_rank;
			}
			if( $last_rank != NULL )	// We opened at least 1 rank div
			{
				echo "</div>\n";
			}
			?>
