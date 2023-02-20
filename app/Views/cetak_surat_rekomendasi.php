<html>
	<head>
		<meta charset="utf-8">
		<title>Surat rekomendasi</title>
		<style>
			.th{
				border: 1px solid #b6bab7;
			    padding: 4px 10px;
			}

			 footer {
                position: fixed; 
                bottom: -60px; 
                left: 0px; 
                right: 0px;
                height: 50px; 

                /** Extra personal styles **/
                color: black;
                text-align: center;
                line-height: 35px;
                font-size: 12px;
                color: #5c5e5d;
            }
		</style>
	</head>			
	<body>

		<?php 
			$image = genarateImageDomPDF("https://apiunit.fkipumkendari.ac.id/assets/kop_surat_baru.png");
		 ?>

		<header style="margin-bottom: 20px;">
			<img src="<?= $image ?>" width="100%" style="object-fit: cover;" />
		</header>

		<div style="margin-left: 50px; margin-right: 50px; text-align: center;">
			<h4>SURAT REKOMENDASI MENGIKUTI MAGANG/ASISTENSI MENGAJAR MERDEKA BELAJAR KAMPUS MEDEKA</h4>
			<br>

			<table>
				<tr>
					<td>NAMA</td>
					<td>: <span style="text-transform: capitalize;"><?= $mahasiswa['nama_lengkap'] ?></span></td>
				</tr>
				<tr>
					<td>STAMBUK</td>
					<td>: <?= $mahasiswa['nim'] ?></td>
				</tr>
				<tr>
					<td>PROGRAM STUDI</td>
					<td>: <span style="text-transform: capitalize;"><?= $mahasiswa['nama_prodi'] ?></span></td>
				</tr>
				<tr>
					<td>PENASEHAT AKADEMIK</td>
					<td>: </td>
				</tr>
			</table>

			<h5>MATAKULIAH YANG DIPROGRAM DALAM MAGANG/ASISTENSI MENGAJAR</h5>

			<table style="width: 100%; border-collapse: collapse; color: #232323;">
				<tr>
					<th class="th" align="center">NO</th>
					<th class="th">MATAKULIAH</th>
					<th class="th" align="center">SKS</th>
				</tr>
				<?php 
					$totalSks = 0;
					foreach ($matakuliah as $key => $row): ?>
					<tr>
						<td class="th" align="center"><?= $key + 1 ?></td>
						<td class="th"><span style="text-transform: capitalize;"><?= $row['matakuliah'] ?></span></td>
						<td class="th" align="center">
							<?= $row['sks'] ?>
						</td>
					</tr>
				<?php $totalSks += (int)$row['sks']; endforeach ?>
				<tfoot>
					<tr>
						<th class="th" colspan="2" align="center">TOTAL SKS</th>
						<th class="th" align="center"><?= $totalSks ?></th>
					</tr>
				</tfoot>
			</table>

			<div style="margin-top: 20px; width: 100%;">
				<div style="width: 50%; float: left; text-align: left ">
					<h4>Direkomendasikan oleh <br>Penasehat Akademik (PA)</h4>
					<br>
					<h4 style="margin-top: 30px;">(..............................................)</h4>
				</div>
				<div style="width: 50%; float: left; text-align: left">
					<h4>Disetujui, <br> Kaprodi </h4>
					<br>
					<h4 style="margin-top: 30px;">
						(..............................................)
					</h4>
				</div>
				<div style="clear: both;">
					
				</div>
			</div>

		</div>

		 <footer>
            Copyright &copy; <?php echo date("Y"); ?> unit.fkipumkendari.ac.id 
        </footer>

	</body>
</html>