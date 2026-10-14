<?php

namespace Src\Domain\BusinessIntelligence\Enums;

enum KPIType: string
{
    // Revenue KPIs
    case REVENUE = 'revenue';
    case PROFIT = 'profit';
    case ARPU = 'arpu'; // Average Revenue Per User
    case MRR = 'mrr'; // Monthly Recurring Revenue
    case ARR = 'arr'; // Annual Recurring Revenue

    // Customer KPIs
    case CUSTOMER_GROWTH = 'customer_growth';
    case CHURN_RATE = 'churn_rate';
    case CUSTOMER_COUNT = 'customer_count';
    case ACTIVE_CUSTOMERS = 'active_customers';
    case NEW_CUSTOMERS = 'new_customers';
    case LOST_CUSTOMERS = 'lost_customers';

    // Financial KPIs
    case OUTSTANDING_INVOICE = 'outstanding_invoice';
    case COLLECTION_RATE = 'collection_rate';
    case AVERAGE_INVOICE_VALUE = 'average_invoice_value';
    case PAYMENT_ON_TIME_RATE = 'payment_on_time_rate';
    case BAD_DEBT_RATE = 'bad_debt_rate';

    // Network KPIs
    case NETWORK_AVAILABILITY = 'network_availability';
    case SLA_COMPLIANCE = 'sla_compliance';
    case BANDWIDTH_USAGE = 'bandwidth_usage';
    case FIBER_UTILIZATION = 'fiber_utilization';
    case OLT_CAPACITY = 'olt_capacity';
    case ODP_CAPACITY = 'odp_capacity';
    case PACKET_LOSS = 'packet_loss';
    case LATENCY = 'latency';

    // Service KPIs
    case TECHNICIAN_PRODUCTIVITY = 'technician_productivity';
    case AVG_RESOLUTION_TIME = 'avg_resolution_time';
    case FIRST_RESPONSE_TIME = 'first_response_time';
    case TICKET_VOLUME = 'ticket_volume';
    case OPEN_TICKETS = 'open_tickets';
    case CLOSED_TICKETS = 'closed_tickets';

    // Installation KPIs
    case INSTALLATION_COUNT = 'installation_count';
    case AVG_INSTALLATION_TIME = 'avg_installation_time';
    case INSTALLATION_SUCCESS_RATE = 'installation_success_rate';
    case PQ_PASS_RATE = 'pq_pass_rate'; // Provisioning Quality

    // Asset KPIs
    case ASSET_UTILIZATION = 'asset_utilization';
    case MAINTENANCE_COST = 'maintenance_cost';
    case EQUIPMENT_UPTIME = 'equipment_uptime';

    public function getLabel(): string
    {
        return match($this) {
            self::REVENUE => 'Total Revenue',
            self::PROFIT => 'Net Profit',
            self::ARPU => 'Average Revenue Per User',
            self::MRR => 'Monthly Recurring Revenue',
            self::ARR => 'Annual Recurring Revenue',
            self::CUSTOMER_GROWTH => 'Customer Growth Rate',
            self::CHURN_RATE => 'Churn Rate',
            self::CUSTOMER_COUNT => 'Total Customers',
            self::ACTIVE_CUSTOMERS => 'Active Customers',
            self::NEW_CUSTOMERS => 'New Customers',
            self::LOST_CUSTOMERS => 'Lost Customers',
            self::OUTSTANDING_INVOICE => 'Outstanding Invoice',
            self::COLLECTION_RATE => 'Collection Rate',
            self::AVERAGE_INVOICE_VALUE => 'Average Invoice Value',
            self::PAYMENT_ON_TIME_RATE => 'Payment On Time Rate',
            self::BAD_DEBT_RATE => 'Bad Debt Rate',
            self::NETWORK_AVAILABILITY => 'Network Availability',
            self::SLA_COMPLIANCE => 'SLA Compliance',
            self::BANDWIDTH_USAGE => 'Bandwidth Usage',
            self::FIBER_UTILIZATION => 'Fiber Utilization',
            self::OLT_CAPACITY => 'OLT Capacity',
            self::ODP_CAPACITY => 'ODP Capacity',
            self::PACKET_LOSS => 'Packet Loss',
            self::LATENCY => 'Network Latency',
            self::TECHNICIAN_PRODUCTIVITY => 'Technician Productivity',
            self::AVG_RESOLUTION_TIME => 'Average Resolution Time',
            self::FIRST_RESPONSE_TIME => 'First Response Time',
            self::TICKET_VOLUME => 'Ticket Volume',
            self::OPEN_TICKETS => 'Open Tickets',
            self::CLOSED_TICKETS => 'Closed Tickets',
            self::INSTALLATION_COUNT => 'Installation Count',
            self::AVG_INSTALLATION_TIME => 'Average Installation Time',
            self::INSTALLATION_SUCCESS_RATE => 'Installation Success Rate',
            self::PQ_PASS_RATE => 'PQ Pass Rate',
            self::ASSET_UTILIZATION => 'Asset Utilization',
            self::MAINTENANCE_COST => 'Maintenance Cost',
            self::EQUIPMENT_UPTIME => 'Equipment Uptime',
        };
    }

    public function getCategory(): string
    {
        return match($this) {
            self::REVENUE, self::PROFIT, self::ARPU, self::MRR, self::ARR => 'Revenue',
            self::CUSTOMER_GROWTH, self::CHURN_RATE, self::CUSTOMER_COUNT,
            self::ACTIVE_CUSTOMERS, self::NEW_CUSTOMERS, self::LOST_CUSTOMERS => 'Customer',
            self::OUTSTANDING_INVOICE, self::COLLECTION_RATE, self::AVERAGE_INVOICE_VALUE,
            self::PAYMENT_ON_TIME_RATE, self::BAD_DEBT_RATE => 'Financial',
            self::NETWORK_AVAILABILITY, self::SLA_COMPLIANCE, self::BANDWIDTH_USAGE,
            self::FIBER_UTILIZATION, self::OLT_CAPACITY, self::ODP_CAPACITY,
            self::PACKET_LOSS, self::LATENCY => 'Network',
            self::TECHNICIAN_PRODUCTIVITY, self::AVG_RESOLUTION_TIME, self::FIRST_RESPONSE_TIME,
            self::TICKET_VOLUME, self::OPEN_TICKETS, self::CLOSED_TICKETS => 'Service',
            self::INSTALLATION_COUNT, self::AVG_INSTALLATION_TIME, self::INSTALLATION_SUCCESS_RATE,
            self::PQ_PASS_RATE => 'Installation',
            self::ASSET_UTILIZATION, self::MAINTENANCE_COST, self::EQUIPMENT_UPTIME => 'Asset',
        };
    }

    public function getUnit(): string
    {
        return match($this) {
            self::REVENUE, self::PROFIT, self::OUTSTANDING_INVOICE, self::MAINTENANCE_COST => 'currency',
            self::ARPU, self::MRR, self::ARR, self::AVERAGE_INVOICE_VALUE => 'currency',
            self::CUSTOMER_GROWTH, self::CHURN_RATE, self::COLLECTION_RATE,
            self::PAYMENT_ON_TIME_RATE, self::BAD_DEBT_RATE, self::NETWORK_AVAILABILITY,
            self::SLA_COMPLIANCE, self::FIBER_UTILIZATION, self::PQ_PASS_RATE,
            self::INSTALLATION_SUCCESS_RATE, self::ASSET_UTILIZATION, self::EQUIPMENT_UPTIME => 'percentage',
            self::CUSTOMER_COUNT, self::ACTIVE_CUSTOMERS, self::NEW_CUSTOMERS,
            self::LOST_CUSTOMERS, self::TICKET_VOLUME, self::OPEN_TICKETS,
            self::CLOSED_TICKETS, self::INSTALLATION_COUNT => 'count',
            self::BANDWIDTH_USAGE => 'bandwidth',
            self::OLT_CAPACITY, self::ODP_CAPACITY => 'percentage',
            self::PACKET_LOSS, self::LATENCY => 'number',
            self::TECHNICIAN_PRODUCTIVITY => 'ratio',
            self::AVG_RESOLUTION_TIME, self::FIRST_RESPONSE_TIME, self::AVG_INSTALLATION_TIME => 'duration',
        };
    }

    public function getAggregationFunction(): string
    {
        return match($this) {
            self::REVENUE, self::PROFIT, self::OUTSTANDING_INVOICE, self::MAINTENANCE_COST => 'sum',
            self::ARPU, self::MRR, self::ARR, self::AVERAGE_INVOICE_VALUE => 'avg',
            self::CUSTOMER_GROWTH, self::CHURN_RATE, self::COLLECTION_RATE,
            self::NETWORK_AVAILABILITY, self::SLA_COMPLIANCE, self::FIBER_UTILIZATION,
            self::PAYMENT_ON_TIME_RATE => 'avg',
            self::CUSTOMER_COUNT, self::ACTIVE_CUSTOMERS, self::NEW_CUSTOMERS,
            self::LOST_CUSTOMERS, self::TICKET_VOLUME, self::OPEN_TICKETS,
            self::CLOSED_TICKETS, self::INSTALLATION_COUNT => 'count',
            default => 'avg',
        };
    }
}
