<?php

namespace YandexTaxiCorpClient;

/** 
 * Класс "Точка маршрута". Состоит из адреса и дополнительных полей. 
 * Адрес может быть получен из ответа методов getAddressByPosition, getNearestPickupPoint или suggestAddresses
 */
class RoutePoint
{
    private Address $address;
    private string $phone;
    private string $comment;
    private string $floor;
    private string $apartment;

    public function __construct(Address $address)
    {
        $this->address = $address;
    }

    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Контактный телефон в точке. Доступен только для первой и последней точки маршрута,
     * и только если в заказе есть требование door_to_door.
     */
    public function setPhone(string $phone)
    {
        $this->phone = $phone;
    }

    public function getPhone()
    {
        return isset($this->phone) ? $this->phone : null;
    }

    /**
     * Комментарий в точке. Доступен только для первой и последней точки маршрута,
     * и только если в заказе есть требование door_to_door.
     */
    public function setComment(string $comment)
    {
        $this->comment = $comment;
    }

    public function getComment()
    {
        return isset($this->comment) ? $this->comment : null;
    }

    /**
     * Этаж в точке. Доступен только для первой и последней точки маршрута,
     * и только если в заказе есть требование door_to_door.
     */
    public function setFloor(string $floor)
    {
        $this->floor = $floor;
    }

    public function getFloor()
    {
        return isset($this->floor) ? $this->floor : null;
    }

    /**
     * Номер квартиры в точке. Доступен только для первой и последней точки маршрута,
     * и только если в заказе есть требование door_to_door.
     */
    public function setApartment(string $apartment)
    {
        $this->apartment = $apartment;
    }

    public function getApartment()
    {
        return isset($this->apartment) ? $this->apartment : null;
    }
}
