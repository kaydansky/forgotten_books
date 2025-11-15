<?php
/**
 * @author: AlexK
 * Date: 27-Jan-19
 * Time: 3:23 PM
 */

namespace ForgottenBooks\Binder;

use ForgottenBooks\Domain\Statistics\StatisticsModel;

class BinderFactory
{
    public function booksWaiting()
    {
        return new BinderProcessor('bindHtmlBooks', 'getBooksWaiting', new StatisticsModel());
    }

    public function booksCompletedWeek()
    {
        return new BinderProcessor('bindHtmlBooks', 'getBooksCompletedWeek', new StatisticsModel());
    }

    public function booksCompletedMonth()
    {
        return new BinderProcessor('bindHtmlBooks', 'getBooksCompletedMonth', new StatisticsModel());
    }

    public function booksCompleted()
    {
        return new BinderProcessor('bindJsonBooks', 'getBooksCompleted', new StatisticsModel());
    }

    public function booksRemoved()
    {
        return new BinderProcessor('bindJsonBooks', 'getBooksRemoved', new StatisticsModel());
    }

    public function users($model, $builder)
    {
        return new BinderProcessor('bindJsonUsers', 'getUserList', $model, null, $builder);
    }

    public function userBooks($model, $id)
    {
        return new BinderProcessor('bindJsonUserBooks', 'getUserBooks', $model, $id);
    }
}