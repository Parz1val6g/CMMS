<?php

namespace App\Features\Tickets\Models;

use App\Core\Enums\TicketPriority;
use App\Core\Enums\TicketStatus;
use App\Core\Traits\Base;
use App\Features\Clients\Models\Client;
use App\Features\Locations\Models\Location;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Shared\Models\User;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use Base;

    protected $fillable = [
        'description',
        'client_id',
        'service_type_id',
        'priority',
        'status',
        'ticket_manager_id',
        'service_order_id',
        'location_id',
    ];

    protected $casts = [
        'priority' => TicketPriority::class,
        'status'   => TicketStatus::class,
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function ticketManager()
    {
        return $this->belongsTo(User::class, 'ticket_manager_id');
    }

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
