<?php

namespace bvdputte\kirbyAutopublish;

class Autopublish {

    public static function publish()
    {
        $kirby = kirby();
        $autopublishfield = option("bvdputte.kirbyAutopublish.fieldName");
        $pagesToPublish = $kirby->collection("autoPublishedDrafts")
                                ->filter(function ($draft) use ($autopublishfield) {
            $publishTime = new \Datetime($draft->$autopublishfield());
            return $publishTime->getTimestamp() < time();
        });

        // kirbylog()->log('test');
        //kirbylog()->log('collection', 'debug', $pagesToPublish);

        // Publish pages which are due
        // kirby()->impersonate("kirby");
        foreach($pagesToPublish as $p) {
            try {
                $p->changeStatus("listed");
                if(function_exists('kirbyLog')) {
                    kirbyLog("autopublish.log")->log("Autopublished " . $p->id(), "info");
                }
            } catch (Exception $e) {
                if(function_exists('kirbyLog')) {
                    kirbyLog("autopublish.log")->log("Error adding " .  $newPage->id() . " to autopublish queue", "error", [$e->getMessage()]);
                } else {
                    error_log("Error adding " .  $newPage->id() . " to autopublish queue");
                }
            }
        }
    }

    public static function unpublish()
    {
        $kirby = kirby();
        $autounpublishfield = option("bvdputte.kirbyAutopublish.fiedNameUnPublish");
        $pagesToUnPublish = $kirby->collection("autoUnPublishListed")
                                ->filter(function ($listed) use ($autounpublishfield) {
            $unPublishTime = new \Datetime($listed->$autounpublishfield());
            return $unPublishTime->getTimestamp() < time();
        });

        // kirbylog()->log('test');
        //kirbylog()->log('collection', 'debug', $pagesToPublish);

        // Publish pages which are due
        // kirby()->impersonate("kirby");
        foreach($pagesToUnPublish as $p) {
            try {
                $p->changeStatus("draft");
                if(function_exists('kirbyLog')) {
                    kirbyLog("autopublish.log")->log("Autounpublished " . $p->id(), "info");
                }
            } catch (Exception $e) {
                if(function_exists('kirbyLog')) {
                    kirbyLog("autopublish.log")->log("Error adding " .  $newPage->id() . " to autounpublish queue", "error", [$e->getMessage()]);
                } else {
                    error_log("Error adding " .  $newPage->id() . " to autounpublish queue");
                }
            }
        }
    }

    public static function poorManCronRun()
    {
        $pmcCache = kirby()->cache("bvdputte.kirbyAutopublish.poormanscron");
        $lastRun = $pmcCache->get("lastrun");


        if ($lastRun === null) {
            self::publish();
            self::unpublish();
            $expire = option("bvdputte.kirbyAutopublish.poormanscron.interval");
            $pmcCache->set("lastrun", time(), $expire);
        }
    }

}