
<html>
	<head>
		<meta charset="utf-8">
		<title>Cetak Nilai</title>
		<link rel="icon" href="<?= base_url('assets/tut.png') ?>">
		<style>
			@import url('https://fonts.googleapis.com/css2?family=Courier+Prime&display=swap');
			body {
				font-family: 'Courier Prime', monospace;
				font-size: 12px;
			}
			.th{
				border: 1px solid #b6bab7;
			    padding: 4px 10px;
			}

		</style>	
	</head>	
	<body>

		<?php 
			$path = base_url('assets/kop_surat_baru.png');
			$image = genarateImageDomPDF($path);
		 ?>
		

		<div style="margin-left: 50px; margin-right: 50px;">
			<header style="margin-bottom: 20px;">
				<img src="<?= $image ?>" width="100%" style="object-fit: cover;" />
			</header>

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
			</table>
			<br />

			<h3 style="margin-left: 8px;">Daftar Matakuliah : </h3>

			<div style="width: 100%;">
				<table style="width: 100%; border-collapse: collapse; color: #232323;">
					<tr style="background-color: #b6bab7;">
						<th class="th">No</th>
						<th class="th">Nama Matakuliah</th>
						<th class="th">SKS</th>
					</tr>
					<?php 
						$jmlSks = 0;
						foreach ($matakuliah as $key => $row): ?>
						<tr>
							<td class="th" class="th" align="center"><?= $key+1 ?></td>
							<td class="th" class="th"><?= $row['matakuliah'] ?></td>
							<td class="th" class="th" align="center"><?= $row['sks'] ?></td>
						</tr>
					<?php $jmlSks += (int)$row['sks']; endforeach ?>
					<tr>
						<th class="th" colspan="2" align="center">
							Jumlah
						</th>
						<th class="th" align="center"><?= $jmlSks ?></th>
					</tr>
				</table>
			</div>	

			<h3 style="margin-left: 8px;">Nilai Akhir : </h3>

			<table style="width: 100%; border-collapse: collapse; color: #232323;">
				<tr style="background-color: #b6bab7;">
					<th class="th" colspan="3">NILAI</th>
				</tr>
				<tr>
					<td class="th" align="center">Indikator penilaian</td>
					<td class="th" align="center">Angka</td>
					<td class="th" align="center">Grade</td>
				</tr>
				<tr>
					<td class="th">Pembekalan</td>
					<td class="th" align="center"><?= $nilai['n_pembekalan'] ?></td>
					<td class="th" rowspan="4" align="center">
						<h1 style="font-size: 35px"><?= $nilai['grade'] ?></h1>
					</td>
				</tr>
				<tr>
					<td class="th">Pelaksanaan</td>
					<td class="th" align="center"><?= $nilai['n_pelaksanaan'] ?></td>
				</tr>
				<tr>
					<td class="th">Laporan</td>
					<td class="th" align="center"><?= $nilai['n_laporan'] ?></td>
				</tr>
				<tr>
					<td class="th">Nilai Akhir</td>
					<td class="th" align="center"><?= $nilai['n_akhir'] ?></td>
				</tr>
			</table>

			<div style="margin-left: 10px;">
				<p style="letter-spacing: -0.5px; font-size:12px; color: #6d6e6d"><i>Dinilai : <?= mediumdate_indo($nilai['updated_at']) ?>, oleh <?= $nilai['nama_dosen'] ?></i> <br />
					<i>Sumber : unit.fkipumkendari.ac.id</i>
				</p>
			</div>

				<?php 
					$pathqrcode = base_url('assets/qrcode/'.$mahasiswa['nim'].'.png');
					$image1 = genarateImageDomPDF($pathqrcode);
				 ?>

			<div style="margin-left: 10px;" style="margin-top: 30px;">
				<h4>Mengetahui :  </h4>
				<h4 style="margin-top: -7px;">Ketua Microteaching, Mitra, dan Desa Binaan <br />
				Fakultas Keguruan dan Ilmu Pendidikan</h4>	

				<img src="<?= $image1 ?>" alt="" width="80px">

				<p>Hasma Nur Jaya, S.Pd, M.Si 
					<br />
					NIDN : 0916128701
				</p>
			</div>

			

		</div>

	</body>
</html>	