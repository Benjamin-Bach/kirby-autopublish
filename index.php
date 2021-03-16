<?php

require __DIR__ . DS . "src" . DS . "Autopublish.php";

// For composer
@include_once __DIR__ . '/vendor/autoload.php';


Kirby::plugin('bvdputte/kirbyAutopublish', [
    'options' => [
        'fieldName' => 'autopublish',
        'fiedNameUnPublish' => 'autounpublish',
        'poormanscron' => false,
        'poormanscron.interval' => 1, // in minutes
        'cache.poormanscron' => true
    ],
    'siteMethods' => [
      'recursiveCollection' => function($root, $status, $site){
        $recursiveCollection = new Collection;
        $recursiveCollection->add($site->find($root)->children()->filterBy('status','unlisted'));
        foreach($site->find($root)->children() as $value) {
          $recursiveCollection->add($value->children()->filterBy('status',$status));
        }
        foreach($site->find($root)->children()->children() as $value) {
          $recursiveCollection->add($value->children()->filterBy('status',$status));
        }
        foreach($site->find($root)->children()->children()->children() as $value) {
          $recursiveCollection->add($value->children()->filterBy('status',$status));
        }
        return $recursiveCollection;
      }
    ],
    'collections' => [
        'autoPublishedDrafts' => function ($site) {
            $autopublishfield = option("bvdputte.kirbyAutopublish.fieldName");
            // $drafts = $site->pages()->drafts(); // Make something more deeper
            $drafts = $site->recursiveCollection('catalogue', 'unlisted', $site);

            $autoPublishedDrafts = $drafts->filter(function ($draft) use ($autopublishfield) {
                return ($draft->$autopublishfield()->exists()) && (!$draft->$autopublishfield()->isEmpty()) && (empty($draft->errors()) === true);
            });


            return $autoPublishedDrafts;
        },
        'autoUnPublishListed' => function ($site){
          $autounpublishfield = option("bvdputte.kirbyAutopublish.fiedNameUnPublish");
          $listeds = $site->recursiveCollection('catalogue', 'listed', $site);

          $autoUnPublishedDrafts = $listeds->filter(function ($listed) use ($autounpublishfield) {
              return ($listed->$autounpublishfield()->exists()) && (!$listed->$autounpublishfield()->isEmpty()) && (empty($listed->errors()) === true);
          });


          return $autoUnPublishedDrafts;

        }
    ],
    'hooks' => [
        'route:before' => function ($route, $path, $method) {
            /*
             * For servers without cron, enable "poormanscron"
             * ⚠️ Ugly, non-performant hack to bypass cache
             * @TODO: Fix this as soon as this is possible:
             * https://github.com/getkirby/ideas/issues/23
             */
            if (option("bvdputte.kirbyAutopublish.poormanscron")) {
              bvdputte\kirbyAutopublish\Autopublish::poorManCronRun();
            }
        }
    ]
]);
