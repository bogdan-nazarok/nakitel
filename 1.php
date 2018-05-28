<?
class Catalog
{
  public $fileNamesURL; // Путь к файлу с именами
  public $dirCatalogURL; // Путь к каталогу с файлами
  public $percent; // Число процента которого будем сравнивать

  // Метод формирования массива каталога с файлами
  public function getDirContents($dir, &$results = array())
  {
    $files = scandir($dir);

    // Формируем массив с именами и путями к файлам
    foreach($files as $file)
    {
      $path = realpath($dir.DIRECTORY_SEPARATOR.$file); 

      if (!is_dir($path))
      {
        // Выберем файлы с расширением .txt
        if (preg_match('/\.txt$/', $path))
        {
          $results[] = array(
            "NAME" => basename($path),
            "PATH" => $path
          ); 
        }
      }
      elseif ($file != "." && $file != "..")
        Catalog::getDirContents($path, $results);
        
    }
    return $results;
  }
  
  // Метод фильтрации и формирования массива сравнивая имена
  public function getFilterNames()
  {
    // Получаем массив списка имен из файла
    if (file_exists($this->fileNamesURL))
      $names = explode(",", file_get_contents($this->fileNamesURL));
    
    // Получаем список файлов c каталога
    $files = $this->getDirContents($this->dirCatalogURL);

    // Формируем массив сравнивая имена
    if (count($files)>0 && count($names)>0)
    {
      foreach($files as $file)
      {
        foreach($names as $name)
        {
          if (!empty($name))
          {
            // Сравниваем имена файлов с именами с файла 
            $st = similar_text(trim($file["NAME"]), trim($name), $similar);
          
            // Фильтруем по числу процента совпадения
            if (intval($similar) <= intval($this->percent))
            {
              $result[] = array(
              	"COMPARABLE_NAME" => trim($name),
                "NAME" => trim($file["NAME"]),
                "PATH" => trim($file["PATH"]),
                "SIMILAR" => intval($similar)."%"
              );
            }
          }
        }
        
        if (count($result)>0)
          $arResult = $result;
      }
    }
    return $arResult;
  }
}

$catalog = new Catalog();
$catalog->fileNamesURL = 'main_list_name.txt'; // Путь к файлу с именами
$catalog->dirCatalogURL = 'main_dir'; // Путь к каталогу с файлами
$catalog->percent = '90'; // Т.к. в задаче стоит найти имена, которые отличаются от списка на 10% и более, то мы будем смотреть на сходства 90% и менее
$arResult = $catalog->getFilterNames();
?>

<?if (count($arResult)>0):?>
	<table cellpadding="10" cellspacing="0" border="1">
		<thead>
			<tr>
				<td align="center"><b>№ п/п</b></td>
				<td align="center"><b>Имя с файла, которое мы сравниваем</b></td>
				<td align="center"><b>Имя файла в каталоге, которое мы сравниваем</b></td>
				<td align="center"><b>Путь к файлу в каталоге</b></td>
				<td align="center"><b>Cтепень похожести двух строк в процентах</b></td>
			</tr>
		</thead>
		<tbody>
			<?
			$i = 0;
			foreach ($arResult as $name => $file):
				$i++;
			?>
				<tr>
					<td align="center"><?=$i?></td>
					<td align="center"><?=$file["COMPARABLE_NAME"]?></td>
					<td align="center"><?=$file["NAME"]?></td>
					<td align="center"><?=$file["PATH"]?></td>
					<td align="center"><?=$file["SIMILAR"]?></td>
				</tr>
			<?endforeach;?>
		</tbody>
	</table>
<?else:?>
	<p>Список пуст.</p>
<?endif;?>
