<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li class="active">Legacy Directional Scan Results</li>
		</ol>
	</div>
	
	<!-- Set Ship Classes -->
	<?php
	$capital = array('Aeon','Archon','Chimera','Wyvern','Revenant','Nyx','Thanatos','Hel','Nidhoggur','Revelation','Phoenix','Moros','Naglfar','Avatar','Leviathan','Erebus','Ragnarok');
	$logistics = array('Apostle','Minokawa','Ninazu','Lif','Deacon','Kirin','Thalia','Scalpel','Guardian','Basilisk','Oneiros','Scimitar','Augoror','Osprey','Exequror','Scythe','Inquisitor','Bantam','Navitas','Burst');
	$support = array('Scorpion','Devoter','Onyx','Phobos','Broadsword','Curse','Pilgrim','Falcon','Rook','Arazu','Lachesis','Huginn','Rapier','Arbitrator','Blackbird','Celestis','Bellicose','Sentinel','Kitsune','Keres','Hyena','Crucifier Navy Issue','Griffin Navy Issue','Maulus Navy Issue','Vigil Fleet Issue','Crucifier','Griffin','Maulus','Vigil','Armageddon','Pontifex','Stork','Magus','Bifrost','Purifier','Manticore','Nemesis','Hound');
	$tackle = array('Heretic','Flycatcher','Eris','Sabre','Confessor','Jackdaw','Hecate','Svipul','Coercer','Dragoon','Cormorant','Corax','Algos','Catalyst','Talwar','Thrasher','Retribution','Vengeance','Harpy','Hawk','Enyo','Ishkur','Jaguar','Wolf','Crusader','Malediction','Crow','Raptor','Ares','Taranis','Claw','Stiletto','Caldari Navy Hookbill','Federation Navy Comet','Imperial Navy Slicer','Republic Fleet Firetail','Cruor','Daredevil','Garmur','Succubus','Worm','Executioner','Punisher','Tormentor','Condor','Kestrel','Merlin','Atron','Incursus','Tristan','Breacher','Rifter','Slasher');
	$combat = array('Absolution','Damnation','Nighthawk','Vulture','Astarte','Eos','Claymore','Sleipnir','Brutix Navy Issue','Drake Navy Issue','Harbinger Navy Issue','Hurricane Fleet Issue','Harbinger','Oracle','Prophecy','Drake','Ferox','Naga','Brutix','Myrmidon','Talos','Cyclone','Hurricane','Tornado','Redeemer','Widow','Sin','Panther','Paladin','Golem','Kronos','Vargur','Apocalypse Navy Issue','Armageddon Navy Issue','Dominix Navy Issue','Megathron Navy Issue','Raven Navy Issue','Scorpion Navy Issue','Tempest Fleet Issue','Typhoon Fleet Issue','Barghest','Machariel','Nestor','Nightmare','Bhaalgorn','Rattlesnake','Vindicator','Abaddon','Apocalypse','Raven','Rokh','Dominix','Hyperion','Megathron','Maelstrom','Tempest','Typhoon','Sacrilege','Zealot','Eagle','Cerberus','Ishtar','Deimos','Muninn','Vagabond','Legion','Tengu','Proteus','Loki','Augoror Navy Issue','Caracal Navy Issue','Exequror Navy Issue','Omen Navy Issue','Osprey Navy Issue','Scythe Fleet Issue','Stabber Fleet Issue','Vexor Navy Issue','Ashimmu','Cynabal','Orthus','Phantasm','Stratios','Gila','Vigilant','Maller','Omen','Caracal','Moa','Thorax','Vexor','Rupture','Stabber','Gnosis');
	$noncombat = array('Anathema','Buzzard','Helios','Cheetah','Magnate','Heron','Imicus','Probe','Impairor','Ibis','Velator','Reaper','Amarr Shuttle','Caldari Shuttle','Gallente Shuttle','Minmatar Shuttle','Capsule');
	$industrial = array('Orca','Rorqual','Providence','Charon','Obelisk','Fenrir','Bowhead','Ark','Rhea','Anshar','Nomad','Endurance','Prospect','Astero','Venture','Impel','Prorator','Bustard','Crane','Occator','Viator','Mastodon','Prowler','Bestower','Sigil','Badger','Tayra','Epithal','Iteron Mark V','Kryos','Miasmos','Nereus','Hoarder','Mammoth','Wreathe','Noctis','Hulk','Mackinaw','Skiff','Covetor','Procurer','Retriever');
	
	$capitalCount = $logisticsCount = $supportCount = $tackleCount = $combatCount = $noncombatCount = $industrialCount = $otherCount = 0;
	$capitalList = $logisticsList = $supportList = $tackleList = $combatList = $noncombatList = $industrialList = $otherList = array();
	
	foreach($scan as $item => $count)
	{
		if(in_array($item,$capital))
		{
			$capitalList[$item] = $count;
			$capitalCount += $count;
		}
		elseif(in_array($item,$logistics))
		{
			$logisticsList[$item] = $count;
			$logisticsCount += $count;
		}
		elseif(in_array($item,$support))
		{
			$supportList[$item] = $count;
			$supportCount += $count;
		}
		elseif(in_array($item,$tackle))
		{
			$tackleList[$item] = $count;
			$tackleCount += $count;
		}
		elseif(in_array($item,$combat))
		{
			$combatList[$item] = $count;
			$combatCount += $count;
		}
		elseif(in_array($item,$noncombat))
		{
			$noncombatList[$item] = $count;
			$noncombatCount += $count;
		}
		elseif(in_array($item,$industrial))
		{
			$industrialList[$item] = $count;
			$industrialCount += $count;
		}
		else
		{
			$otherList[$item] = $count;
			$otherCount += $count;
		}
		
	}
	?>
	
	
	<div class="row">

		<header class="page-header col-md-10 col-md-offset-1">
			
			<h2 class="page-title full-page-title">
				Legacy DScan<?php
				echo ' (';
				if( $filter == Tool_model::DSCAN_FILTER_ONGRID )
				{
					echo 'On-Grid';
				}
				elseif( $filter == Tool_model::DSCAN_FILTER_OFFGRID )
				{
					echo 'Off-Grid';
				}
				else
				{
					echo 'Everything';
				}
				echo ') on '.$date->format( 'F jS \a\t H:i:s' ); ?>
			</h2>
			
		</header>
		
		<article style="font-size:14px;" class="col-lg-10 col-lg-offset-1">

			<div class="row">
			
				<div class="col-md-3 col-sm-4">		
					<h3>
						Capitals (<?php echo $capitalCount; ?>)
					</h3>		
					<div class="table-responsive">
						<table class="table table-bordered table-striped table_valign_m">
							<tbody><?php
								if(empty($capitalList))
								{
									echo '<tr><td>0</td><td style="width:100%;overflow-wrap:break-word;">None!</a></td></tr>';
								}
								foreach($capitalList as $item => $count)
								{
									echo '<tr><td class="aligncenter">'.$count.'</td><td style="width:100%;overflow-wrap:break-word;">'.$item.'</a></td></tr>';
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="col-md-3 col-sm-4">		
					<h3>
						Combat (<?php echo $combatCount; ?>)
					</h3>		
					<div class="table-responsive">
						<table class="table table-bordered table-striped table_valign_m">
							<tbody><?php
								if(empty($combatList))
								{
									echo '<tr><td>0</td><td style="width:100%;overflow-wrap:break-word;">None!</a></td></tr>';
								}
								foreach($combatList as $item => $count)
								{
									echo '<tr><td class="aligncenter">'.$count.'</td><td style="width:100%;overflow-wrap:break-word;">'.$item.'</a></td></tr>';
								}?>
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="col-md-3 col-sm-4">		
					<h3>
						Logistics (<?php echo $logisticsCount; ?>)
					</h3>		
					<div class="table-responsive">
						<table class="table table-bordered table-striped table_valign_m">
							<tbody><?php
								if(empty($logisticsList))
								{
									echo '<tr><td>0</td><td style="width:100%;overflow-wrap:break-word;">None!</a></td></tr>';
								}
								foreach($logisticsList as $item => $count)
								{
									echo '<tr><td class="aligncenter">'.$count.'</td><td style="width:100%;overflow-wrap:break-word;">'.$item.'</a></td></tr>';
								}?>
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="col-md-3 col-sm-4">		
					<h3>
						Support (<?php echo $supportCount; ?>)
					</h3>		
					<div class="table-responsive">
						<table class="table table-bordered table-striped table_valign_m">
							<tbody><?php
								if(empty($supportList))
								{
									echo '<tr><td>0</td><td style="width:100%;overflow-wrap:break-word;">None!</a></td></tr>';
								}
								foreach($supportList as $item => $count)
								{
									echo '<tr><td class="aligncenter">'.$count.'</td><td style="width:100%;overflow-wrap:break-word;">'.$item.'</a></td></tr>';
								}?>
							</tbody>
						</table>
					</div>
				</div>
				
			</div>
			
			<div class="row">
			
				<div class="col-md-3 col-sm-4">		
					<h3>
						Tackle (<?php echo $tackleCount; ?>)
					</h3>		
					<div class="table-responsive">
						<table class="table table-bordered table-striped table_valign_m">
							<tbody><?php
								if(empty($tackleList))
								{
									echo '<tr><td>0</td><td style="width:100%;overflow-wrap:break-word;">None!</a></td></tr>';
								}
								foreach($tackleList as $item => $count)
								{
									echo '<tr><td class="aligncenter">'.$count.'</td><td style="width:100%;overflow-wrap:break-word;">'.$item.'</a></td></tr>';
								}?>
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="col-md-3 col-sm-4">		
					<h3>
						Passive (<?php echo $noncombatCount; ?>)
					</h3>		
					<div class="table-responsive">
						<table class="table table-bordered table-striped table_valign_m">
							<tbody><?php
								if(empty($noncombatList))
								{
									echo '<tr><td>0</td><td style="width:100%;overflow-wrap:break-word;">None!</a></td></tr>';
								}
								foreach($noncombatList as $item => $count)
								{
									echo '<tr><td class="aligncenter">'.$count.'</td><td style="width:100%;overflow-wrap:break-word;">'.$item.'</a></td></tr>';
								}?>
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="col-md-3 col-sm-4">		
					<h3>
						Industrial (<?php echo $industrialCount; ?>)
					</h3>		
					<div class="table-responsive">
						<table class="table table-bordered table-striped table_valign_m">
							<tbody><?php
								if(empty($industrialList))
								{
									echo '<tr><td>0</td><td style="width:100%;overflow-wrap:break-word;">None!</a></td></tr>';
								}
								foreach($industrialList as $item => $count)
								{
									echo '<tr><td class="aligncenter">'.$count.'</td><td style="width:100%;overflow-wrap:break-word;">'.$item.'</a></td></tr>';
								}?>
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="col-md-3 col-sm-8">		
					<h3>
						Other (<?php echo $otherCount; ?>)
					</h3>		
					<div class="table-responsive">
						<table class="table table-bordered table-striped table_valign_m">
							<tbody><?php
								if(empty($otherList))
								{
									echo '<tr><td>0</td><td style="width:100%;overflow-wrap:break-word;">None!</a></td></tr>';
								}
								foreach($otherList as $item => $count)
								{
									echo '<tr><td class="aligncenter">'.$count.'</td><td style="width:100%;overflow-wrap:break-word;">'.$item.'</a></td></tr>';
								}?>
							</tbody>
						</table>
					</div>
				</div>
			
			</div>
			
		</article>

	</div>

</div><!--/#content-->