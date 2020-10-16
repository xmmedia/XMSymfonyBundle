<?= $model; ?>Mutation:
    decorator: true
    heirs: [Mutation]
    config:
        fields:
            <?= $model; ?>Add:
                type: <?= $model; ?>MutationPayload!
                description: 'Add <?= $model; ?>.'
                public: '@=isAuthenticated()'
                access: '@=isAuthenticated()'
                resolve: '@=mutation("<?= $mutation_add; ?>", [args["<?= $model_lower; ?>"]])'
                args:
                    <?= $model_lower; ?>: <?= $model; ?>Input!

            <?= $model; ?>Update:
                type: <?= $model; ?>MutationPayload!
                description: 'Update <?= $model; ?>.'
                public: '@=isAuthenticated()'
                access: '@=isAuthenticated()'
                resolve: '@=mutation("<?= $mutation_update; ?>", [args["<?= $model_lower; ?>"]])'
                args:
                    <?= $model_lower; ?>: <?= $model; ?>Input!

            <?= $model; ?>Delete:
                type: <?= $model; ?>DeleteMutationPayload!
                description: 'Delete <?= $model; ?>.'
                public: '@=isAuthenticated()'
                access: '@=isAuthenticated()'
                resolve: '@=mutation("<?= $mutation_delete; ?>", [args["<?= $id_property; ?>"]])'
                args:
                    <?= $id_property; ?>: UUID!

<?= $model; ?>Input:
    type: input-object
    config:
        description: '<?= $model; ?> add & update mutation input.'
        fields:
            <?= $id_property; ?>:
                type: UUID!
            name:
                type: String!

<?= $model; ?>MutationPayload:
    type: object
    config:
        description: '<?= $model; ?> mutation payload.'
        fields:
            <?= $id_property; ?>:
                type: UUID!


<?= $model; ?>DeleteMutationPayload:
    type: object
    config:
        description: '<?= $model; ?> delete mutation payload.'
        fields:
            success:
                type: Boolean!
