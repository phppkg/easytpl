this is an layout file.

--- head

<?php $this->block('header') ?>
on layout: block header;
<?php $this->endblock(); ?>

--- body

<?php $this->block('body') ?>
on layout: block body;
<?php $this->endblock(); ?>

--- footer

<?php $this->block('footer') ?>
on layout: block footer;
<?php $this->endblock(); ?>

