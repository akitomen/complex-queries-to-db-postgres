<table class="table">
	<thead>
	<tr>
		<th scope="col">City</th>
		<th scope="col">Count</th>
	</tr>
	</thead>
	<tbody>
    <?php
    foreach ($points as $point) { ?>
		<tr>
			<th scope="row"><?= $point['city'] ?></th>
			<td><?= $point['count'] ?></td>
		</tr>
    <?php
    } ?>
	</tbody>
</table>
