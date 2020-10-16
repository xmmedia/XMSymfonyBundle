<?= $model; ?>:
    type: object
    config:
        description: 'The <?= $model; ?> object.'
        fields:
            <?= $id_property; ?>:
                type: UUID!
            name:
                type: String!
