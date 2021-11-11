<?php
/**
 * comments
 *
 * @var array $map
 * @var object $obj
 */
?>

echo vars:

<?= $map['name'] ?>
<?php echo $map['name'] ?>

foreach example:

<?php foreach ($map as $key => $val) : ?>
        KEY:<?= $key?> => VALUE:<?php
    $typ = gettype($val);
    echo ucfirst($typ === 'array' ? 'arrayValue' : $typ)
?>

in foreach
<?php endforeach ?>

<?php
// define var
$a = random_int(1, 10);
?>

if example1:

<?php if ($a < 2): ?>
  at if
<?php endif ?>

if example2:

<?php if ($a < 2) { echo "at if\n"; }?>

if example3:

<?php if ($a < 2){ ?>
  at if
<?php } ?>

if-elseif-else example1:

<?php if ($a < 3): ?>
  at if
<?php elseif ($a > 5) : ?>
  at elseif
<?php else : ?>
  at else
<?php endif ?>

if-elseif-else example2:

<?php if ($a < 3) {
  echo "at if\n";
} elseif ($a > 5) {
  echo "at elseif\n";
} else {
    echo "at else\n";
}
?>

switch example:

<?php switch ($a): ?>
<?php case 3:
        break;
  ?>

<?php case 5: ?>
    <?php break ?>
<?php endswitch; ?>

